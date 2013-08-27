<?php

namespace Zoop\Shard\Test\Reference;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;

class ReferenceMapTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'documents' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.reference' => true
                ],
                'document_manager' => 'testing.documentmanager',
                'service_manager_config' => [
                    'factories' => [
                        'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                    ]
                ]
            ]
        );

        $this->referenceMap = $manifest->getServiceManager()->get('referenceMap');
    }

    public function testReferenceMap()
    {
        $map = $this->referenceMap->getMap();

        $this->assertCount(1, $map['Zoop\Shard\Test\Reference\TestAsset\Document\Country']);
        $this->assertEquals('country', $map['Zoop\Shard\Test\Reference\TestAsset\Document\Country'][0]['field']);
    }
}
