<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */

namespace trntv\filekit\widget;

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
class Upload extends InputWidget
{
    /**
     * @var
     */
    public $files;
    /**
     * @var array|\ArrayObject
     */
    public $url;
    /**
     * @var array
     */
    public $clientOptions = [];
    /**
     * @var bool
     */
    public $multiple = false;
    /**
     * @var bool
     */
    public $sortable = false;
    /**
     * @var int
     */
    public $maxNumberOfFiles = 1;
    /**
     * @var int max file size in bytes
     */
    public $maxFileSize;
    /**
     * @var string regexp
     */
    public $acceptFileTypes;

    public $fileStorage = 'fileStorage';

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if ($this->maxNumberOfFiles > 1) {
            $this->multiple = true;
        }
        if ($this->hasModel()) {
            $this->name = $this->name ?: Html::getInputName($this->model, $this->attribute);
            $this->value = $this->value ?: Html::getAttributeValue($this->model, $this->attribute);
        }
        if (!array_key_exists('name', $this->clientOptions)) {
            $this->clientOptions['name'] = $this->name;
        }
        if ($this->multiple && $this->value && !is_array($this->value)) {
            throw new InvalidParamException('In "multiple" mode, value must be an array.');
        }
        if (!array_key_exists('fileparam', $this->url)) {
            $this->url['fileparam'] = $this->getFileInputName();
        }
        if (!$this->files && $this->value) {
            $this->files = $this->multiple ? $this->value : [$this->value];
        }

        $this->clientOptions = ArrayHelper::merge(
            $this->clientOptions,
            [
                'url' => Url::to($this->url),
                'multiple' => $this->multiple,
                'sortable' => $this->sortable,
                'maxNumberOfFiles' => $this->maxNumberOfFiles,
                'maxFileSize' => $this->maxFileSize,
                'acceptFileTypes' => $this->acceptFileTypes,
                'files' => $this->files
            ]
        );
    }

    /**
     * @return string
     */
    public function getFileInputName()
    {
        return sprintf('_fileinput_%s', $this->id);
    }

    /**
     * @return string
     */
    public function run()
    {
        $this->registerClientScript();
        $content = Html::beginTag('div');
        $content .= Html::hiddenInput($this->name, null, [
            'class' => 'empty-value',
            'id' => $this->options['id']
        ]);
        $content .= Html::fileInput($this->getFileInputName(), null, [
            'name' => $this->getFileInputName(),
            'id' => $this->getId(),
            'multiple' => $this->multiple
        ]);
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
        if ($this->sortable) {
            JuiAsset::register($this->getView());
        }
        $this->getView()->registerJs("jQuery('#{$this->getId()}').yiiUploadKit({$options});");
    }
}
