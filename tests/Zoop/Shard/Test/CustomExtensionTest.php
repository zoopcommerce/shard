<?php

namespace Zoop\Shard\Test;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;

class CustomExtensionTest extends BaseTest
{

    public function testCustomExtensionLoad()
    {
        $manifest = new Manifest(
            [
                'extension_configs' => [
                    'extension.custom' => true
                ],
                'service_manager_config' => [
                    'factories' => [
                        'extension.custom' => 'Zoop\Shard\Test\TestAsset\CustomExtension\ExtensionFactory'
                    ]
                ]
            ]
        );

        $extension = $manifest->getServiceManager()->get('extension.custom');
        
        $this->assertEquals('extension loaded', $extension->getMessage());
    }
}
