<?php
namespace trntv\filekit\storage\repository;

use Exception;
use trntv\filekit\storage\File;
use trntv\filekit\storage\models\FileStorageItem;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class BaseRepository
 * @package common\components\fileStorage\repository
 */
abstract class BaseRepository extends Component{

    public $name;
    /**
     * Event triggered after delete
     */
    const EVENT_AFTER_DELETE = 'afterDelete';
    /**
     * Event triggered after save
     */
    const EVENT_AFTER_SAVE = 'afterSave';

    public $createDbRecord = true;
    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(){
        if(!$this->name){
            throw new InvalidConfigException(\Yii::t('extensions/filekit', 'Name cannot be empty'));
        }
    }

    /**
     * This method is called at the end saving a file.
     * Method creates a record about saved file to db
     * @param $file
     * @param null $category
     * @throws \Exception
     */
    public function afterSave($file, $category = null){
        if(!$file->error && $this->createDbRecord) {
            $model = new FileStorageItem();
            $model->repository = $this->name;
            $model->category = $category;
            $model->url = $file->url;
            $model->path = $file->path->getPath();
            $model->size = $file->size;
            $model->mimeType = $file->mimeType;
            $model->status = FileStorageItem::STATUS_UPLOADED;
            if(!$model->save()){
                throw new Exception($model->errors);
            };
        }
        $this->trigger(self::EVENT_AFTER_SAVE);
    }

    /**
     * @param $file
     */
    public function afterDelete($file){
        if($this->createDbRecord) {
            $model = FileStorageItem::findOne(['path' => $file->path, 'repository' => $this->name]);
            if ($model) {
                $model->status = FileStorageItem::STATUS_DELETED;
                $model->save(false);
            }
        }
        $this->trigger(self::EVENT_AFTER_DELETE);
    }

    /**
     * @param File $file
     * @return mixed
     */
    public function delete(File $file){
        $this->afterDelete($file);
    }

    /**
     * @param File $file
     * @param $category
     * @return mixed
     */
    public function save(File $file, $category = null){
        $this->afterSave($file, $category);
    }

    /**
     * @return mixed
     */
    abstract public function reset();
}