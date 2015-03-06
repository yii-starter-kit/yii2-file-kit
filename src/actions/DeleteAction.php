<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 7/13/14
 * Time: 1:20 PM
 */

namespace trntv\filekit\actions;

use yii\web\HttpException;

/**
 * public function actions(){
 *   return [
 *           'upload'=>[
 *               'class'=>'trntv\filekit\actions\DeleteAction',
 *           ]
 *       ];
 *   }
 */
class DeleteAction extends BaseAction
{
    /**
     * @var string path request param
     */
    public $pathParam = 'path';
    /**
     * @return bool
     * @throws HttpException
     * @throws \HttpException
     */
    public function run()
    {
        $path = \Yii::$app->request->get($this->pathParam);
        $paths = \Yii::$app->session->get($this->sessionKey, []);
        if (in_array($path, $paths, true)) {
            $success = $this->getFileStorage()->delete($path);
            if (!$success) {
                throw new HttpException(400);
            }
            return $success;
        } else {
            throw new HttpException(403);
        }
    }
}
