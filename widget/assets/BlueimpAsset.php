<?php
namespace trntv\filekit\widget\assets;

use yii\web\AssetBundle;

class BlueimpAsset extends AssetBundle
{
    public function init(){
        $this->sourcePath = __DIR__ . '/static';
        parent::init();
    }

    public $css = [
        'blueimp-file-upload/css/jquery.fileupload.css'
    ];

    public $js = [
        'blueimp-file-upload/js/vendor/jquery.ui.widget.js',
        'blueimp-file-upload/js/jquery.iframe-transport.js',
        'blueimp-file-upload/js/jquery.fileupload.js',
        'blueimp-file-upload/js/jquery.fileupload-process.js',
        'blueimp-file-upload/js/jquery.fileupload-validate.js',
        'blueimp-load-image/js/load-image.min.js',
        'blueimp-canvas-to-blob/js/canvas-to-blob.min.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
} 