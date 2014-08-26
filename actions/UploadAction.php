<?php
namespace trntv\filekit\actions;

use trntv\filekit\storage\File;
use yii\base\Action;
use yii\base\Exception;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\UploadedFile;

/**
* public function actions(){
*   return [
*           'upload'=>[
*               'class'=>'trntv\filekit\actions\UploadAction',
*               'responseUrlParam'=>'file-url',
 *              'fileProcessing'=>function($file, $uploadAction){
                    // do something
 *              }
*           ]
*       ];
*   }
*/


class UploadAction extends Action
{
    public $fileStorage = 'fileStorage';
    public $fileparam = 'file';

    public $model;
    public $attribute;

    public $fileCategory;
    public $fileCategoryParam = 'category';

    public $repository;
    public $disableCsrf = false;

    // todo: Check types, max size, max count, etc;

    public $responseFormat = Response::FORMAT_JSON;
    public $responsePathParam = false;
    public $responseUrlParam = 'url';
    public $responseExtensionParam = 'extension';
    public $responseMimeTypeParam = 'mimeType';
    public $responseSizeParam = 'size';

    /**
     * @var \Closure
     * ```php
     * function($file, $uploadAction) {
     *     // process file value
     * }
     * ```
     */
    public $fileProcessing;

    public function init(){
        \Yii::$app->response->format = $this->responseFormat;
        if(\Yii::$app->request->get('fileparam')){
            $this->fileparam = \Yii::$app->request->get('fileparam');
        }
        if($this->model && $this->attribute){
            $this->fileparam = Html::getInputName($this->model, $this->attribute);
        }
        if(!$this->fileCategory){
            $this->fileCategory = \Yii::$app->request->get($this->fileCategoryParam);
        }
        if($this->disableCsrf){
            \Yii::$app->request->enableCsrfValidation = false;
        }
    }

    public function run()
    {
        $result = [];
        $files = UploadedFile::getInstancesByName($this->fileparam);
        foreach ($files as $file) {
            $file = File::load($file);
            $file = \Yii::$app->{$this->fileStorage}->save($file, $this->fileCategory, $this->repository);
            if (!$file->error) {
                if ($this->fileProcessing instanceof \Closure) {
                    call_user_func($this->fileProcessing, $file, $this);
                }
                $output = [
                    $this->responseUrlParam => $file->url,
                    $this->responseExtensionParam => $file->path->extension,
                    $this->responseMimeTypeParam => $file->mimeType,
                    $this->responseSizeParam => $file->size,
                ];
                if($this->responsePathParam){
                    $output[$this->responsePathParam] = (string) $file->path;
                }
                $result[] = $output;
            } else {
                throw new Exception($file->error);
            }
        }
        return $result;
    }

} 