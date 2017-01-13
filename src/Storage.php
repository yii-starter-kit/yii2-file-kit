<?php
namespace trntv\filekit;

use Yii;
use League\Flysystem\FilesystemInterface;
use trntv\filekit\events\StorageEvent;
use trntv\filekit\filesystem\FilesystemBuilderInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class Storage
 * @package trntv\filekit
 * @author Eugene Terentev <eugene@terentev.net>
 */
class Storage extends Component
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
            $this->baseUrl = Yii::getAlias($this->baseUrl);
        }

        if ($this->filesystemComponent !== null) {
            $this->filesystem = Yii::$app->get($this->filesystemComponent);
        } else {
            $this->filesystem = Yii::createObject($this->filesystem);
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
     * @param $file string|\yii\web\UploadedFile
     * @param bool $preserveFileName
     * @param bool $overwrite
     * @param array $config
     * @return bool|string
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function save($file, $preserveFileName = false, $overwrite = false, $config = [])
    {
        $fileObj = File::create($file);
        $dirIndex = $this->getDirIndex();
        if ($preserveFileName === false) {
            do {
                $filename = implode('.', [
                    Yii::$app->security->generateRandomString(),
                    $fileObj->getExtension()
                ]);
                $path = implode('/', [$dirIndex, $filename]);
            } while ($this->getFilesystem()->has($path));
        } else {
            $filename = $fileObj->getPathInfo('filename');
            $path = implode('/', [$dirIndex, $filename]);
        }

        $this->beforeSave($fileObj->getPath(), $this->getFilesystem());

        $stream = fopen($fileObj->getPath(), 'rb+');

        $config = array_merge(['ContentType' => $fileObj->getMimeType()], $config);
        if ($overwrite) {
            $success = $this->getFilesystem()->putStream($path, $stream, $config);
        } else {
            $success = $this->getFilesystem()->writeStream($path, $stream, $config);
        }

		if (is_resource($stream)) {
			fclose($stream);
		}

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
     * @param array $config
     * @return array
     */
    public function saveAll($files, $preserveFileName = false, $overwrite = false, array $config = [])
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = $this->save($file, $preserveFileName, $overwrite, $config);
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
            $this->getFilesystem()->write('.dirindex', (string) $this->dirindex);
        } else {
            $this->dirindex = $this->getFilesystem()->read('.dirindex');
            if ($this->maxDirFiles !== -1) {
                $filesCount = count($this->getFilesystem()->listContents($this->dirindex));
                if ($filesCount > $this->maxDirFiles) {
                    $this->dirindex++;
                    $this->getFilesystem()->put('.dirindex', (string) $this->dirindex);
                }
            }
        }
        return $this->dirindex;
    }

    /**
     * @param $path
     * @param null|\League\Flysystem\FilesystemInterface $filesystem
     * @throws InvalidConfigException
     */
    public function beforeSave($path, $filesystem = null)
    {
        /* @var \trntv\filekit\events\StorageEvent $event */
        $event = Yii::createObject([
            'class' => StorageEvent::className(),
            'path' => $path,
            'filesystem' => $filesystem
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
        $event = Yii::createObject([
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
        $event = Yii::createObject([
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
        $event = Yii::createObject([
            'class' => StorageEvent::className(),
            'path' => $path,
            'filesystem' => $filesystem
        ]);
        $this->trigger(self::EVENT_AFTER_DELETE, $event);
    }
}
