<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */

namespace trntv\filekit\widget;


class SingleFileUpload extends Upload{
    public $multiple = false;
    public function init()
    {
        $this->fileuploadOptions['maxNumberOfFiles'] = 1;
        parent::init();
    }
} 