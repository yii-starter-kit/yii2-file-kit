<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 7/13/14
 * Time: 1:20 PM
 */

namespace trntv\filekit\actions;

use yii\base\Action;

/**
 * public function actions(){
 *   return [
 *           'upload'=>[
 *               'class'=>'trntv\filekit\actions\DeleteAction',
 *           ]
 *       ];
 *   }
 */
class DeleteAction extends Action
{
    public $fileStorage = 'fileStorage';
    public $fileparam = 'path';
    public $repositoryparam = 'repository';

    public function run()
    {
        return \Yii::$app->{$this->fileStorage}->delete(
            \Yii::$app->request->get($this->fileparam),
            \Yii::$app->request->get($this->repositoryparam)
        );
    }
} 