<?php

namespace trntv\filekit\tests;

use trntv\filekit\Storage;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class StorageTest extends TestCase
{
    public function testInitWithBuilder(): void
    {
        $storage = new Storage([
            'filesystem' => [
                'class' => 'trntv\filekit\tests\data\TmpFilesystemBuilder'
            ]
        ]);

        $this->assertNotNull($storage->getFilesystem());
    }
}
