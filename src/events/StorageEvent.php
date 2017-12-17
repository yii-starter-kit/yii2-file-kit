<?php

namespace trntv\filekit\events;

use yii\base\Event;

/**
 * Class StorageEvent
 * @package trntv\filekit\events
 * @author Eugene Terentev <eugene@terentev.net>
 */
class StorageEvent extends Event
{
    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    public $filesystem;
    /**
     * @var string
     */
    public $path;
	
	/**
     * original filename
     * @var type 
     */
    public $oriName;
    
    /**
     * file size (bytes)
     * @var int
     */
    public $size;
    
    /**
     * mime type
     * @var string
     */
    public $mimeType;
}
