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
     * @var \trntv\filekit\File
     */
    public $file;
    /**
     * @var string
     */
    public $path;
}
