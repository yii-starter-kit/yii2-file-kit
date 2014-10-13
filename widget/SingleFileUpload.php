<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */

namespace trntv\filekit\widget;


/**
 * Class SingleFileUpload
 * @package trntv\filekit\widget
 */
class SingleFileUpload extends Upload{
    /**
     * @var bool
     */
    public $multiple = false;

    /**
     * @throws \yii\base\InvalidParamException
     */
    public function init()
    {
        $this->fileuploadOptions['maxNumberOfFiles'] = 1;
        parent::init();
    }
} 