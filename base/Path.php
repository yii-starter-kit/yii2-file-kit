<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */
namespace trntv\filekit\base;

use yii\base\Object;
use yii\helpers\FileHelper;

class Path extends Object{

    private $_dirname;
    private $_filename;
    private $_extension;
    private $_originalPath;

    public function setPath($path)
    {
        $path = FileHelper::normalizePath($path);
        $this->_originalPath = $path;
        $this->_dirname = pathinfo($path, PATHINFO_DIRNAME);
        $this->_filename = pathinfo($path, PATHINFO_FILENAME);
        $this->_extension = pathinfo($path, PATHINFO_EXTENSION );
        return $path;
    }

    public function getPath()
    {
        return sprintf("%s".DIRECTORY_SEPARATOR."%s", $this->_dirname, $this->getBasename());
    }

    public function getOriginalPath()
    {
        return $this->_originalPath;
    }

    public function setDirname($dirname)
    {
        return $this->_dirname = $dirname;
    }

    public function setFilename($filename)
    {
        return $this->_filename = $filename;
    }

    public function setExtension($extension)
    {
        return $this->_extension = $extension;
    }

    public function getDirname()
    {
        return $this->_dirname;
    }

    public function getFilename()
    {
        return $this->_filename;
    }

    public function getBasename()
    {
        if($this->_extension){
            return sprintf("%s.%s", $this->_filename, $this->_extension);
        } else {
            return $this->_filename;
        }
    }

    public function addFilenamePrefix($prefix)
    {
        $this->setFilename($prefix.$this->_filename);
        return $this;
    }

    public function addFilenameSuffix($suffix)
    {
        $this->setFilename($this->_filename.$suffix);
        return $this;
    }

    public function getExtension()
    {
        return $this->_extension;
    }

    public function __toString()
    {
        return $this->getPath();
    }

}