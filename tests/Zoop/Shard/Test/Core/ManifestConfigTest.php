<?php

namespace Zoop\Shard\Test\Core;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;

class ManifestConfigTest extends BaseTest
{

    public function testDefaultConfig()
    {
        $db = 'zoop-shard';
        $manifest = new Manifest(
            [
                'extension_configs' => [
                    'extension.serializer' => true,
                    'extension.odmcore' => $this->getOdmCoreConfig()
                ]
            ]
        );
        /* @var $core CoreExtension */
        $core = $manifest->getServiceManager()->get('extension.odmcore');

        $this->assertEquals($db, $core->getDefaultDb());
    }

    public function testMergedConfig()
    {
        $db = 'zoop-shard-test';
        $manifest = new Manifest(
            [
                'extension_configs' => [
                    'extension.serializer' => true,
                    'extension.odmcore' => [
                        'default_db' => $db
                    ]
                ]
            ]
        );
        /* @var $core CoreExtension */
        $core = $manifest->getServiceManager()->get('extension.odmcore');

        $this->assertEquals($db, $core->getDefaultDb());
    }
}
