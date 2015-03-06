<?php
namespace trntv\filekit\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class FilesBehavior
 * @author Eugene Terentev <eugene@terentev.net>
 */
class FilesBehavior extends Behavior
{
    /**
     * @var ActiveRecord
     */
    public $owner;
    /**
     * @var string Model attribute that contain uploaded files array
     */
    public $filesAttribute = 'files';
    /**
     * @var string name of the relation
     */
    public $filesRelation;
    /**
     * @var $fileModel
     * Schema example:
     *      `id` INT NOT NULL AUTO_INCREMENT,
     *      `path` VARCHAR(1024) NOT NULL,
     *      `baseUrl` VARCHAR(255) NULL,
     *      `type` VARCHAR(255) NULL,
     *      `size` INT NULL,
     *      `name` VARCHAR(255) NULL,
     *      `foreign_key_id` INT NOT NULL,
     */
    public $fileModel;
    /**
     * @var string
     */
    public $fileModelScenario = 'default';

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
     *
     */
    public function init()
    {
        if (!$this->fileModel) {
            $this->fileModel = $this->getFilesRelation()->modelClass;
        }
    }

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind'
        ];
    }


    /**
     *
     */
    public function afterInsert()
    {
        if ($this->owner->{$this->filesAttribute}) {
            $this->saveFiles($this->owner->{$this->filesAttribute});
        }
    }

    /**
     * @throws \Exception
     */
    public function afterUpdate()
    {
        $filesPaths = ArrayHelper::getColumn($this->getFiles(), 'path');
        $models = $this->owner->getRelation($this->filesRelation)->all();
        $modelsPaths = ArrayHelper::getColumn($models, 'path');
        $newFiles = [];
        foreach ($models as $model) {
            if (!in_array($model->path, $filesPaths, true)) {
                $model->delete();
                $this->getStorage()->delete($model->path);
            }
        }
        foreach ($this->getFiles() as $file) {
            if (!in_array($file['path'], $modelsPaths, true)) {
                $newFiles[] = $file;
            }
        }
        $this->saveFiles($newFiles);
    }

    /**
     *
     */
    public function beforeDelete()
    {
        $this->deletePaths = ArrayHelper::getColumn($this->getFiles(), 'path');
    }

    /**
     *
     */
    public function afterDelete()
    {
        $this->getStorage()->deleteAll($this->deletePaths);
    }

    /**
     *
     */
    public function afterFind()
    {
        $this->owner->{$this->filesAttribute} = ArrayHelper::toArray($this->owner->{$this->filesRelation});
    }

    /**
     * @param array $files
     */
    protected function saveFiles($files)
    {
        foreach ($files as $file) {
            $model = new $this->fileModel;
            $model->setScenario($this->fileModelScenario);
            $model->load($file, '');
            $this->owner->link($this->filesRelation, $model);
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
    protected function getFiles()
    {
        $files = $this->owner->{$this->filesAttribute};
        return $files ?: [];
    }

    /**
     * @return \yii\db\ActiveQuery|\yii\db\ActiveQueryInterface
     */
    protected function getFilesRelation()
    {
        return $this->owner->getRelation($this->filesRelation);
    }
}
