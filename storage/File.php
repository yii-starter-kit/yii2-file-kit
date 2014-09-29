<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 7/13/14
 * Time: 2:00 PM
 */
namespace trntv\filekit\storage;

use trntv\filekit\base\Path;
use trntv\filekit\base\Url;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Class File
 * @package trntv\filekit\storage
 * @property \trntv\filekit\base\Path $path
 */
class File extends Object
{
    public $is_stored = false;
    public $error = false;
    public $extension;
    public $url;

    private $_path;
    private $_size;
    private $_mimeType;

    /**
     * Init file
     */
    public function init(){
        if(!$this->path){
            throw new InvalidCallException;
        }
        if(!$this->extension){
            $this->extension = $this->_path->extension;
        }
    }

    /**
     * @return mixed
     */
    public function getSize(){
        if(!$this->_size){
            $this->_size = filesize($this->path);
        }
        return $this->_size;
    }

    public function getMimeType(){
        if(!$this->_mimeType){
            $this->_mimeType = FileHelper::getMimeType($this->_path);
        }
        return $this->_mimeType;
    }

    public function getExtensionByMimeType()
    {
        $extensions = FileHelper::getExtensionsByMimeType($this->getMimeType());
        return array_shift($extensions);
    }

    public function setPath($path){
        if(!is_a($path, Path::className())){
            $path = \Yii::createObject([
                'class'=>'trntv\filekit\base\Path',
                'path'=>$path
            ]);
        }
        $this->_path = $path;
    }


    /**
     * @return \trntv\filekit\base\Path
     */
    public function getPath(){
        return $this->_path;
    }

    /**
     * @param $file
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public static function load($file){

        if(is_a($file, self::className())){
            return $file;
        }

        // UploadedFile
        if(is_a($file, UploadedFile::className())){
            if($file->error){
                throw new InvalidParamException("File upload error \"{$file->error}\"");
            }
            return \Yii::createObject([
                'class'=>self::className(),
                'path'=>$file->tempName,
                'extension'=>$file->getExtension()
            ]);
        }

        // Path
        else {
            return \Yii::createObject([
                'class' => self::className(),
                'path' => FileHelper::normalizePath($file)
            ]);
        }

        return false;
    }

    /**
     * @param array $files
     * @return array
     */
    public static function loadMulti(array $files){
        $result = [];
        foreach($files as $file){
            $result[] = self::load($file);
        }
        return $result;
    }

    public function hasErrors(){
        return $this->error !== false;
    }

    /**
     * @param $url
     * @param $path
     * @return bool|string Downloaded file path
     */
    public static function download($url, $path = false)
    {
        if(!$path){
            $path = tempnam(sys_get_temp_dir(), 'yii');
        };
        // todo: rewrite using stream
        if(!($file = file_get_contents($url)) || file_put_contents($path, $file) === false){
            return false;
        }
        return $path;
    }
}