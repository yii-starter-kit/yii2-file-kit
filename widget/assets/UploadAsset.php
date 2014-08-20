<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */

namespace trntv\filekit\widget\assets;

use yii\web\AssetBundle;

class UploadAsset extends AssetBundle{

    public function init(){
        $this->sourcePath = __DIR__ . '/upload';
        parent::init();
    }

    public $css = [
        'css/upload-kit.css'
    ];

    public $js = [
        'js/upload-kit.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'trntv\filekit\widget\assets\BlueimpAsset',
    ];
} 