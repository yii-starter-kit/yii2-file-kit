<?php
namespace trntv\filekit\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class UploadBehavior
 * @author Eugene Terentev <eugene@terentev.net>
 */
class UploadBehavior extends Behavior
{
    /**
     * @var ActiveRecord
     */
    public $owner;

    /**
     * @var string Model attribute that contain uploaded file information
     * or array of files information
     */
    public $attribute = 'file';

    /**
     * @var bool
     */
    public $multiple = false;

    /**
     * @var
     */
    public $attributePrefix;
    /**
     * @var string
     */
    public $pathAttribute;
    /**
     * @var string
     */
    public $baseUrlAttribute;
    /**
     * @var string
     */
    public $typeAttribute;
    /**
     * @var string
     */
    public $sizeAttribute;
    /**
     * @var string
     */
    public $nameAttribute;
    /**
     * @var string
     */
    public $orderAttribute;

    /**
     * @var string name of the relation
     */
    public $uploadRelation;
    /**
     * @var $uploadModel
     * Schema example:
     *      `id` INT NOT NULL AUTO_INCREMENT,
     *      `path` VARCHAR(1024) NOT NULL,
     *      `base_url` VARCHAR(255) NULL,
     *      `type` VARCHAR(255) NULL,
     *      `size` INT NULL,
     *      `name` VARCHAR(255) NULL,
     *      `order` INT NULL,
     *      `foreign_key_id` INT NOT NULL,
     */
    public $uploadModel;
    /**
     * @var string
     */
    public $uploadModelScenario = 'default';

    /**
     * @var string
     */
    public $filesStorage = 'fileStorage';

    /**
     * @var array
     */
    protected $deletePaths;
    /**
     * @var \trntv\filekit\Storage
     */
    protected $storage;
    /**
     * @return array
     */
    public function events()
    {
        $multipleEvents = [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFindMultiple',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsertMultiple',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdateMultiple',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDeleteMultiple',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete'
        ];

        $singleEvents = [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFindSingle',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'afterValidateSingle',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdateSingle',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdateSingle',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDeleteSingle',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete'
        ];

        return $this->multiple ? $multipleEvents : $singleEvents;
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = [
            'path' => $this->pathAttribute,
            'base_url' => $this->baseUrlAttribute,
            'type' => $this->typeAttribute,
            'size' => $this->sizeAttribute,
            'name' => $this->nameAttribute,
            'order' => $this->orderAttribute
        ];

        if ($this->attributePrefix !== null) {
            $fields = array_map(function ($fieldName) {
                return $this->attributePrefix . $fieldName;
            }, $fields);
        }

        return $fields;
    }

    /**
     *
     */
    public function afterValidateSingle()
    {
        $this->loadModel($this->owner, $this->owner->{$this->attribute});
    }

    /**
     *
     */
    public function afterInsertMultiple()
    {
        if ($this->owner->{$this->attribute}) {
            $this->saveFilesToRelation($this->owner->{$this->attribute});
        }
    }

    /**
     * @throws \Exception
     */
    public function afterUpdateMultiple()
    {
        $filesPaths = ArrayHelper::getColumn($this->getUploaded(), 'path');
        $models = $this->owner->getRelation($this->uploadRelation)->all();
        $modelsPaths = ArrayHelper::getColumn($models, $this->getAttributeField('path'));
        $newFiles = [];
        foreach ($models as $model) {
            $path = $model->getAttribute($this->getAttributeField('path'));
            if (!in_array($path, $filesPaths, true) && $model->delete()) {
                $this->getStorage()->delete($path);
            }
        }
        foreach ($this->getUploaded() as $file) {
            if (!in_array($file['path'], $modelsPaths, true)) {
                $newFiles[] = $file;
            }
        }
        $this->saveFilesToRelation($newFiles);
    }

    /**
     *
     */
    public function beforeUpdateSingle()
    {
        $this->deletePaths = $this->owner->{$this->getAttributeField('path')};
    }

    /**
     *
     */
    public function afterUpdateSingle()
    {
        $path = $this->owner->{$this->getAttributeField('path')};
        if ($this->deletePaths !== $path) {
            $this->deleteFiles();
        }
    }

    /**
     *
     */
    public function beforeDeleteMultiple()
    {
        $this->deletePaths = ArrayHelper::getColumn($this->getUploaded(), 'path');
    }

    /**
     *
     */
    public function beforeDeleteSingle()
    {
        $this->deletePaths = $this->owner->{$this->getAttributeField('path')};
    }

    /**
     *
     */
    public function afterDelete()
    {
        $this->deletePaths = is_array($this->deletePaths) ? array_filter($this->deletePaths) : $this->deletePaths;
        $this->deleteFiles();
    }

    /**
     *
     */
    public function afterFindMultiple()
    {
        $models = $this->owner->{$this->uploadRelation};
        $fields = $this->fields();
        $data = [];
        foreach ($models as $k => $model) {
            /* @var $model \yii\db\BaseActiveRecord */
            $file = [];
            foreach ($fields as $dataField => $modelAttribute) {
                $file[$dataField] = $model->hasAttribute($modelAttribute)
                    ? ArrayHelper::getValue($model, $modelAttribute)
                    : null;
            }
            if ($file['path']) {
                $data[$k] = $this->enrichFileData($file);
            }

        }
        $this->owner->{$this->attribute} = $data;
    }

    /**
     *
     */
    public function afterFindSingle()
    {
        $file = array_map(function ($attribute) {
            return $this->owner->getAttribute($attribute);
        }, $this->fields());
        if ($file['path']) {
            $this->owner->{$this->attribute} = $this->enrichFileData($file);
        }
    }

    /**
     * @return string
     */
    public function getUploadModelClass()
    {
        if (!$this->uploadModel) {
            $this->uploadModel = $this->getUploadRelation()->modelClass;
        }
        return $this->uploadModel;
    }

    /**
     * @param array $files
     */
    protected function saveFilesToRelation($files)
    {
        $modelClass = $this->getUploadModelClass();
        foreach ($files as $file) {
            $model = new $modelClass;
            $model->setScenario($this->uploadModelScenario);
            $model = $this->loadModel($model, $file);
            if ($this->getUploadRelation()->via !== null) {
                $model->save(false);
            }
            $this->owner->link($this->uploadRelation, $model);
        }
    }

    /**
     * @return \trntv\filekit\Storage
     * @throws \yii\base\InvalidConfigException
     */
    protected function getStorage()
    {
        if (!$this->storage) {
            $this->storage = Yii::$app->get($this->filesStorage);
        }
        return $this->storage;

    }

    /**
     * @return array
     */
    protected function getUploaded()
    {
        $files = $this->owner->{$this->attribute};
        return $files ?: [];
    }

    /**
     * @return \yii\db\ActiveQuery|\yii\db\ActiveQueryInterface
     */
    protected function getUploadRelation()
    {
        return $this->owner->getRelation($this->uploadRelation);
    }

    /**
     * @param $model \yii\db\ActiveRecord
     * @param $data
     * @return \yii\db\ActiveRecord
     */
    protected function loadModel(&$model, $data)
    {
        $attributes = array_flip($model->attributes());
        foreach ($this->fields() as $dataField => $modelField) {
            if ($modelField && array_key_exists($modelField, $attributes)) {
                $model->{$modelField} =  ArrayHelper::getValue($data, $dataField);
            }
        }
        return $model;
    }

    /**
     * @param $type
     * @return mixed
     */
    protected function getAttributeField($type)
    {
        return ArrayHelper::getValue($this->fields(), $type, false);
    }

    /**
     * @return bool|void
     */
    protected function deleteFiles()
    {
        $storage = $this->getStorage();
        if ($this->deletePaths !== null) {
            return is_array($this->deletePaths)
                ? $storage->deleteAll($this->deletePaths)
                : $storage->delete($this->deletePaths);
        }
        return true;
    }

    /**
     * @param $file
     * @return mixed
     */
    protected function enrichFileData($file)
    {
        $fs = $this->getStorage()->getFilesystem();
        if ($file['path'] && $fs->has($file['path'])) {
            $data = [
                'type' => $fs->getMimetype($file['path']),
                'size' => $fs->getSize($file['path']),
                'timestamp' => $fs->getTimestamp($file['path'])
            ];
            foreach ($data as $k => $v) {
                if (!array_key_exists($k, $file) || !$file[$k]) {
                    $file[$k] = $v;
                }
            }
        }
        return $file;
    }
}
