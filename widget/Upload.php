<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */

namespace trntv\filekit\widget;

use trntv\filekit\widget\assets\UploadAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\InputWidget;

class Upload extends InputWidget{
    public $url;
    public $clientOptions = [];

    public function init(){
        parent::init();
        if(!isset($this->url['fileparam'])){
            if($this->name) {
                $this->url['fileparam'] = $this->name;
            } else {
                $this->url['fileparam'] = Html::getInputName($this->model, $this->attribute);
            }
        }
        $this->clientOptions['url'] = $this->url !== null ? Url::to($this->url) : '';
    }

    public function run()
    {
        $this->registerClientScript();
        $content = Html::beginTag('div', $this->options);
        $content .= $this->hasModel()
            ? Html::activeFileInput($this->model, $this->attribute)
            : Html::fileInput($this->name, $this->value);
        $content .= Html::endTag('div');
        return $content;

    }

    /**
     * Registers required script for the plugin to work as jQuery File Uploader
     */
    public function registerClientScript()
    {
        UploadAsset::register($this->getView());
        $options = Json::encode($this->clientOptions);
        $id = $this->options['id'];
        $this->getView()->registerJs("jQuery('#{$id}').yiiUploadKit({$options});");
    }
} 