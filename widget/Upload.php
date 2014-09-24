<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */

namespace trntv\filekit\widget;

use trntv\filekit\widget\assets\UploadAsset;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\InputWidget;

class Upload extends InputWidget{
    public $url;
    public $clientOptions = [];
    public $fileuploadOptions = [];
    public $multiple = true;

    public function init(){
        parent::init();
        if($this->hasModel()){
            $this->name = $this->name ?: Html::getInputName($this->model, $this->attribute);
            $this->value = $this->value ?: Html::getAttributeValue($this->model, $this->attribute);
        }
        if($this->multiple && $this->value && !is_array($this->value)){
            throw new InvalidParamException('In "multiple" mode, value must be an array. Use SingleUpload widget instead or set Upload::multiple to "false"');
        }
        if(!isset($this->url['fileparam'])){
            if($this->name) {
                $this->url['fileparam'] = $this->name;
            } else {
                $this->url['fileparam'] = Html::getInputName($this->model, $this->attribute);
            }
        }
        $this->clientOptions['url'] = $this->url !== null && is_array($this->url) ? Url::to($this->url) : '';
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
        if($this->value){
            if($this->multiple){
                foreach($this->value as $v){
                    $content .= Html::hiddenInput(sprintf('%s[]', $this->name), $v);
                }
            } else {
                $content .= Html::hiddenInput($this->name, $this->value);
            }
        }
        $content .= Html::hiddenInput($this->name, null, ['class'=>'empty-value']);
        $content .= Html::fileInput($this->name, null, $this->options);
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