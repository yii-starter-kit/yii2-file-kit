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
     *     // do some stuff before file saved
     * }
     * ```
     */
    public $beforeFileSaved;

    /**
     * @var \Closure
     * ```php
     * function($file, $uploadAction) {
     *     // process file value
     * }
     * ```
     */
    public $fileProcessing;

    /**
     * @var \Closure
     * ```php
     * function($response, $uploadAction) {
     *     // process $response
     * }
     * ```
     */
    public $beforeResponse;

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
        $files = UploadedFile::getInstancesByName($this->fileparam);

        //collect files to save
        $filesToSave = [];
        foreach ($files as $file) {
            $file = File::load($file);

            //with custom user function defined under $this->beforeFileSaved you can skip some files from saving or throw an error to stop the process of uploading
            $allowToSave = true;
            if ($this->beforeFileSaved instanceof \Closure) {
                $allowToSave = call_user_func($this->beforeFileSaved, $file, $this);
            }

            //if ($allowToSave === false), then skip the file
            if ($allowToSave !== false) {
                $filesToSave[] = $file;
            }
        }

        $result = [];
        foreach ($filesToSave as $file) {
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

        //process final response if needed
        if ($this->beforeResponse instanceof \Closure) {
            $userResponse = call_user_func($this->beforeResponse, $result, $this);
            if ($userResponse) {
                $result = $userResponse;
            }
        }

        return $result;
    }

} 
