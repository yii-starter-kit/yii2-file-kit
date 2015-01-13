<?php
namespace trntv\filekit\storage\repository;

use trntv\filekit\storage\File;
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

    public $recordClass;
    public $createRecord = true;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(){
        if(!$this->name){
            throw new InvalidConfigException('Name cannot be empty');
        }
        if($this->recordClass && !class_implements($this->recordClass, 'trntv\filekit\base\FileRecordInterface')){
            throw new InvalidConfigException('BaseRepository::recordClass must implement trntv\filekit\base\FileRecordInterface');
        }
    }

    /**
     * This method is called at the end saving a file.
     * Method creates a record about saved file to db
     * @param \trntv\filekit\base\File $file
     * @param null $category
     * @throws \Exception
     */
    public function afterSave($file, $category = null){
        if($this->createRecord && $this->recordClass && !$file->error) {
            $path = \Yii::createObject([
                'class'=>$this->recordClass,
                'repository'=>$this->name,
                'size' => $file->size,
                'mimeType' => $file->mimeType,
                'basename' => $file->path->get
            ]);
            if(!$record->save()){
                throw new Exception($record->errors);
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