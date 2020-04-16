<?php
namespace trntv\filekit\widget;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@npm/fortawesome--fontawesome';

    public $css= [
        'css/fontawesome.min.css'
    ];
}
