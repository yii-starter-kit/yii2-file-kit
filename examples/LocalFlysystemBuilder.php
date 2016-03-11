<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use yii\base\Object;

/**
 * Class LocalFilesystemBuilder
 * @author Eugene Terentev <eugene@terentev.net>*
 *
 */
class LocalFilesystemBuilder extends Object implements \trntv\filekit\filesystem\FilesystemBuilderInterface
{
    /**
     * @var
     */
    public $path;

    /**
     * @return Filesystem
     */
    public function build()
    {
        $adapter = new Local(\Yii::getAlias($this->path));
        return new Filesystem($adapter);
    }
}