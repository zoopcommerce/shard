<?php

namespace Zoop\Shard\Test\Rest;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;

class RestTest extends BaseTest {

    public function setUp(){

        $manifest = new Manifest([
            'documents' => [
                __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
            ],
            'extension_configs' => [
                'extension.rest' => [
                    'endpoint_map' => [
                        'simple' => [
                            'class' => 'Zoop\Shard\Test\Rest\TestAsset\Document\Simple',
                            'property' => 'id',
                            'cache_control' => [
                                'public'  => true,
                                'max_age' => 10
                            ]
                        ]
                    ]
                ]
            ],
            'document_manager' => 'testing.documentmanager',
            'service_manager_config' => [
                'factories' => [
                    'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                ]
            ]
        ]);

        $this->endpointMap = $manifest->getServiceManager()->get('endpointmap');
    }

    public function testHas(){

        $this->assertTrue($this->endpointMap->hasEndpoint('simple'));
        $this->assertFalse($this->endpointMap->hasEndpoint('does not exist'));
    }

    public function testGet(){
        $endpoint = $this->endpointMap->getEndpoint('simple');
        $this->assertEquals('Zoop\Shard\Test\Rest\TestAsset\Document\Simple', $endpoint->getClass());
    }

    public function testCacheControl(){
        $cacheOptions = $this->endpointMap->getEndpoint('simple')->getCacheControl();
        $this->assertTrue($cacheOptions->getPublic());
        $this->assertEquals(10, $cacheOptions->getMaxAge());
    }
}