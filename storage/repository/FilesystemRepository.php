<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 7/13/14
 * Time: 2:02 PM
 */

namespace trntv\filekit\storage\repository;

use trntv\filekit\storage\File;
use trntv\filekit\storage\models\FileStorageItem;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

/**
 * Class FilesystemRepository
 * @package common\components\fileStorage\repository
 */
class FilesystemRepository extends BaseRepository{

    public $name = 'filesystem';
    /**
     * Storage path
     * @var
     */
    public $basePath;
    /**
     * Base url for stored files
     * @var
     */
    public $baseUrl;
    /**
     * Max files in directory
     * @var int
     */
    public $maxFiles = 65535; // Default: Fat32 limit

    /**
     * @var int
     */
    private $_dirindex = 1;
    /**
     * @var
     */
    private $_dirmtime;
    /**
     * @var
     */
    private $_filescount;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(){
        if(!$this->basePath){
            throw new InvalidConfigException;
        }

        $this->basePath = \Yii::getAlias($this->basePath);
        $this->baseUrl = \Yii::getAlias($this->baseUrl);

        if(!file_exists($this->basePath.'/'.$this->getDirIndex())){
            mkdir($this->basePath.'/'.$this->getDirIndex());
        }

        if(!$this->baseUrl){
            $this->baseUrl = \Yii::getAlias('@webroot');
        }

        if(!$this->name){
            throw new InvalidConfigException;
        }
    }

    /**
     * @return int
     */
    public function getDirIndex(){
        if(!file_exists($this->basePath.'/index')){
            file_put_contents($this->basePath.'/index', $this->_dirindex);
        } else {
            $this->_dirindex = intval(file_get_contents($this->basePath.'/index'));
        }

        if(file_exists($this->basePath.'/'.$this->_dirindex)){
            if((count(scandir($this->basePath.'/'.$this->_dirindex)) - 2) > $this->maxFiles){
                $this->_dirindex++;
                file_put_contents($this->basePath.'/index', $this->_dirindex);
            }
        }
        return $this->_dirindex;
    }

    /**
     * @return int
     */
    public function getFilesCount(){
        if (filemtime($this->basePath . '/' . $this->_dirindex) > $this->_dirmtime) {
            $this->_dirmtime = filemtime($this->basePath . '/' . $this->_dirindex);
            $this->_filescount = count(scandir($this->basePath . '/' . $this->_dirindex)) - 2;
        }
        return $this->_filescount;
    }

    /**
     * @param File $file
     * @param bool $category
     * @return File
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    public function save(File $file, $category = false)
    {
        if(!file_exists($this->basePath.'/'.$this->getDirIndex())){
            mkdir($this->basePath.'/'.$this->getDirIndex());
        }
        $i = 0;
        do{
            $name = sprintf('%s.%s', \Yii::$app->security->generateRandomString(), $file->extension);
            $basename = $this->getDirIndex().'/'.$name;
            $path = $this->basePath . '/' . $basename;
            $url = $this->baseUrl . '/' . $basename;
            if(++$i > 50) throw new Exception(\Yii::t('extensions/filekit', 'FileStorage: cannot generate file name'));
        } while(file_exists($path) || FileStorageItem::find()->where(['url'=>$url])->count());

        if(rename($file->path, $this->basePath.'/'.$basename)){
            $file->is_stored = true;
            $file->path = $path;
            $file->url = $url;
        } else {
            $file->error = true;
        };
        $this->afterSave($file, $category);
        return $file;
    }

    /**
     * @param File $file
     * @return bool
     */
    public function delete(File $file){
        if(unlink($file->path)){
            $this->afterDelete($file);
            return true;
        };
        return false;
    }

    /**
     * Reset storage
     */
    public function reset()
    {
        FileHelper::removeDirectory($this->basePath);
        FileStorageItem::deleteAll(['repository'=>$this->name]);
        mkdir($this->basePath);
    }
}