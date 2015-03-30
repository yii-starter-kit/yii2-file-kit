<?php
namespace trntv\filekit;

use League\Flysystem\FilesystemInterface;
use trntv\filekit\events\StorageEvent;
use trntv\filekit\filesystem\FilesystemBuilderInterface;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;

/**
 * Class Storage
 * @package trntv\filekit
 * @author Eugene Terentev <eugene@terentev.net>
 */
class Storage extends \yii\base\Component implements BootstrapInterface
{

    /**
     * Event triggered after delete
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';
    /**
     * Event triggered after save
     */
    const EVENT_BEFORE_SAVE = 'beforeSave';
    /**
     * Event triggered after delete
     */
    const EVENT_AFTER_DELETE = 'afterDelete';
    /**
     * Event triggered after save
     */
    const EVENT_AFTER_SAVE = 'afterSave';
    /**
     * @var
     */
    public $baseUrl;
    /**
     * @var
     */
    public $filesystemComponent;
    /**
     * @var
     */
    protected $filesystem;
    /**
     * Max files in directory
     * "-1" = unlimited
     * @var int
     */
    public $maxDirFiles = 65535; // Default: Fat32 limit
    /**
     * @var int
     */
    private $dirindex = 1;
    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->baseUrl !== null) {
            $this->baseUrl = \Yii::getAlias($this->baseUrl);
        }

        if ($this->filesystemComponent !== null) {
            $this->filesystem = \Yii::$app->get($this->filesystemComponent);
        } else {
            $this->filesystem = \Yii::createObject($this->filesystem);
            if ($this->filesystem instanceof FilesystemBuilderInterface) {
                $this->filesystem = $this->filesystem->build();
            }
        }
    }

    /**
     * @return FilesystemInterface
     * @throws InvalidConfigException
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @param $filesystem
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        if ($app->getI18n() && !array_key_exists('extensions/trntv/filekit', $app->getI18n()->translations)) {
            $app->getI18n()->translations['extensions/trntv/filekit'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@trntv/filekit/messages',
                'fileMap'=>[
                    'extensions/trntv/filekit'=>'filekit.php'
                ]
            ];
        }
    }

    /**
     * @param $file string|\yii\web\UploadedFile
     * @param bool $preserveFileName
     * @param bool $overwrite
     * @return bool|string
     */
    public function save($file, $preserveFileName = false, $overwrite = false)
    {
        $fileObj = File::create($file);
        if ($preserveFileName === false) {
            do {
                $filename = implode('.', [
                    \Yii::$app->security->generateRandomString(),
                    $fileObj->getExtension()
                ]);
                $path = implode('/', [$this->dirindex, $filename]);
            } while ($this->getFilesystem()->has($path));
        } else {
            $filename = $fileObj->getPathInfo('filename');
            $path = implode('/', [$this->dirindex, $filename]);
        }

        $this->beforeSave($fileObj->getPath(), $this->getFilesystem());

        $stream = fopen($fileObj->getPath(), 'r+');
        if ($overwrite) {
            $success = $this->getFilesystem()->putStream($path, $stream);
        } else {
            $success = $this->getFilesystem()->writeStream($path, $stream);
        }
        fclose($stream);

        if ($success) {
            $this->afterSave($path, $this->getFilesystem());
            return $path;
        }

        return false;

    }

    /**
     * @param $files array|\yii\web\UploadedFile[]
     * @param bool $preserveFileName
     * @param bool $overwrite
     * @return array
     */
    public function saveAll($files, $preserveFileName = false, $overwrite = false)
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = $this->save($file, $preserveFileName, $overwrite);
        }
        return $paths;
    }

    /**
     * @param $path
     * @return bool
     */
    public function delete($path)
    {
        if ($this->getFilesystem()->has($path)) {
            $this->beforeDelete($path, $this->getFilesystem());
            if ($this->getFilesystem()->delete($path)) {
                $this->afterDelete($path, $this->getFilesystem());
                return true;
            };
        }
        return false;
    }

    /**
     * @param $files
     */
    public function deleteAll($files)
    {
        foreach ($files as $file) {
            $this->delete($file);
        }

    }

    /**
     * @return false|int|string
     */
    protected function getDirIndex()
    {
        if (!$this->getFilesystem()->has('.dirindex')) {
            $this->getFilesystem()->write('.dirindex', $this->dirindex);
        } else {
            $this->dirindex = $this->getFilesystem()->read('.dirindex');
            if ($this->maxDirFiles !== -1) {
                $filesCount =  $this->getFilesystem()->listContents($this->dirindex);
                if ($filesCount > $this->maxDirFiles) {
                    $this->dirindex++;
                    $this->getFilesystem()->write('.dirindex', $this->dirindex);
                }
            }
        }
        return $this->dirindex;
    }

    /**
     * @param $path
     * @throws InvalidConfigException
     */
    public function beforeSave($path)
    {
        /* @var \trntv\filekit\events\StorageEvent $event */
        $event = \Yii::createObject([
            'class' => StorageEvent::className(),
            'path' => $path
        ]);
        $this->trigger(self::EVENT_BEFORE_SAVE, $event);
    }

    /**
     * @param $path
     * @param $filesystem
     * @throws InvalidConfigException
     */
    public function afterSave($path, $filesystem)
    {
        /* @var \trntv\filekit\events\StorageEvent $event */
        $event = \Yii::createObject([
            'class' => StorageEvent::className(),
            'path' => $path,
            'filesystem' => $filesystem
        ]);
        $this->trigger(self::EVENT_AFTER_SAVE, $event);
    }

    /**
     * @param $path
     * @param $filesystem
     * @throws InvalidConfigException
     */
    public function beforeDelete($path, $filesystem)
    {
        /* @var \trntv\filekit\events\StorageEvent $event */
        $event = \Yii::createObject([
            'class' => StorageEvent::className(),
            'path' => $path,
            'filesystem' => $filesystem
        ]);
        $this->trigger(self::EVENT_BEFORE_DELETE, $event);
    }

    /**
     * @param $path
     * @param $filesystem
     * @throws InvalidConfigException
     */
    public function afterDelete($path, $filesystem)
    {
        /* @var \trntv\filekit\events\StorageEvent $event */
        $event = \Yii::createObject([
            'class' => StorageEvent::className(),
            'path' => $path,
            'filesystem' => $filesystem
        ]);
        $this->trigger(self::EVENT_AFTER_DELETE, $event);
    }
}
