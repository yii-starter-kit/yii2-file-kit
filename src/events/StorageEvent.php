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
     * @var \League\Flysystem\FilesystemOperator
     */
    public $filesystem;
    /**
     * @var string
     */
    public $path;
}
