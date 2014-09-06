<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */

namespace trntv\filekit\widget;


class SingleFileUpload extends Upload{
    public function init()
    {
        $this->fileuploadOptions['maxNumberOfFiles'] = 1;
        if($this->value)
        parent::init();
    }
} 