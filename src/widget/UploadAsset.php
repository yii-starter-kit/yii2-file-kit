<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */

namespace trntv\filekit\widget;

use yii\web\AssetBundle;

class UploadAsset extends AssetBundle
{

    public $sourcePath = '@trntv/filekit/widget/assets';

    public $css = [
        'css/upload-kit.css'
    ];

    public $js = [
        'js/upload-kit.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'trntv\filekit\widget\BlueimpFileuploadAsset'
    ];
}
