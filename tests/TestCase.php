<?php

namespace trntv\filekit\tests;

use yii\di\Container;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the base class for all yii framework unit tests.
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mockApplication();
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    protected function mockApplication($config = [], $appClass = '\yii\web\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'components' => [
                'request' => [
                    'cookieValidationKey' => 'MD44rEeFtNSeJ37sOzD954sI',
                    'scriptFile' => __DIR__ .'/index.php',
                    'scriptUrl' => '/index.php',
                ],
            ]
        ], $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
    }
}
