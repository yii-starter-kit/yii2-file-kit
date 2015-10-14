<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */

namespace trntv\filekit\widget;

use yii\web\AssetBundle;

class UploadAsset extends AssetBundle
{
    public $css = [
        'css/upload-kit.min.css'
    ];

    public $js = [
        'js/upload-kit.min.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'trntv\filekit\widget\BlueimpFileuploadAsset'
    ];

    public function init()
    {
        $this->sourcePath = __DIR__."/assets";
        parent::init();
    }
}
