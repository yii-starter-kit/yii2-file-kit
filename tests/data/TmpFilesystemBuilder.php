<?php
namespace trntv\filekit\tests\data;

use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;
use trntv\filekit\filesystem\FilesystemBuilderInterface;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class TmpFilesystemBuilder implements FilesystemBuilderInterface
{

    /**
     * @return mixed
     */
    public function build()
    {
        return new Filesystem(new LocalFilesystemAdapter(sys_get_temp_dir()));
    }
}
