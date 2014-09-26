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
use yii\jui\JuiAsset;
use yii\widgets\InputWidget;

/**
 * Class Upload
 * @package trntv\filekit\widget
 */
class Upload extends InputWidget{
    /**
     * @var Upload url
     */
    public $url;
    /**
     * @var array
     */
    public $clientOptions;
    /**
     * @var array
     */
    public $fileuploadOptions;
    /**
     * @var bool
     */
    public $multiple = true;
    /**
     * @var bool
     */
    public $sortable = false;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(){
        parent::init();
        if($this->hasModel()){
            $this->name = $this->name ?: Html::getInputName($this->model, $this->attribute);
            $this->value = $this->value ?: Html::getAttributeValue($this->model, $this->attribute);
        }
        if($this->multiple && $this->value && !is_array($this->value)){
            throw new InvalidParamException('In "multiple" mode, value must be an array. Use SingleUpload widget or set Upload::multiple to "false"');
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
            $this->clientOptions,
            [
                'multiple'=>$this->multiple,
                'sortable'=>$this->sortable
            ]);
    }

    /**
     * @return string
     */
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
        if(!$this->sortable){
            JuiAsset::register($this->getView());
        }
        $this->getView()->registerJs("jQuery('#{$id}').yiiUploadKit({$options});");
    }
} 