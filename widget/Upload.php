<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */

namespace trntv\filekit\widget;

use trntv\filekit\widget\assets\UploadAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\InputWidget;

class Upload extends InputWidget{
    public $url;
    public $clientOptions = [];
    public $fileuploadOptions = [];


    public function init(){
        parent::init();
        if(!isset($this->url['fileparam'])){
            if($this->name) {
                $this->url['fileparam'] = $this->name;
            } else {
                $this->url['fileparam'] = Html::getInputName($this->model, $this->attribute);
            }
        }
        $this->clientOptions['url'] = $this->url !== null && is_array($this->url) ? Url::to($this->url) : '';
        if($this->hasModel()){
            $this->name = $this->name ?: Html::getInputName($this->model, $this->attribute);
            $this->value = $this->value ?: $this->model->{$this->attribute};
        }
        $this->clientOptions = ArrayHelper::merge(
            [
                'fileuploadOptions'=>$this->fileuploadOptions
            ],
            $this->clientOptions);
    }

    public function run()
    {
        $this->registerClientScript();
        $content = Html::beginTag('div');
        if($this->value && is_array($this->value)){
            foreach($this->value as $v){
                $content .= Html::hiddenInput(sprintf('%s[]', $this->name), $v);
            }
            $content .= Html::fileInput($this->name, null, $this->options);
        } else {
            $content .= Html::fileInput($this->name, $this->value, $this->options);
        }
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