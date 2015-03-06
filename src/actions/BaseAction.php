<?php
namespace trntv\filekit\actions;

use yii\base\Action;

/**
 * Class BaseAction
 * @package trntv\filekit\actions
 * @author Eugene Terentev <eugene@terentev.net>
 */
abstract class BaseAction extends Action
{
    /**
     * @var string file storage component name
     */
    public $fileStorage = 'fileStorage';
    /**
     * @var string Request param name that provides file storage component name
     */
    public $fileStorageParam = 'fileStorage';
    /**
     * @var string session key to store list of uploaded files
     */
    public $sessionKey = '_uploadedFiles';

    /**
     * @return \trntv\filekit\Storage
     * @throws \HttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function getFileStorage()
    {
        $fileStorageId = \Yii::$app->request->get($this->fileStorageParam, $this->fileStorage);
        $fileStorage = \Yii::$app->get($fileStorageId);
        if (!$fileStorage) {
            throw new \HttpException(400);
        }
        return $fileStorage;
    }
}
