<?php

namespace Zoop\Shard\Test\Serializer;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Flavour;

class SerializerCustomTypeSerializerTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'object_map' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.serializer' => [
                        'type_serializers' => [
                            'string' => 'stringTypeSerializer'
                        ]
                    ],
                    'extension.odmcore' => true
                ],
            ]
        );

        $manifest->getServiceManager()
            ->setInvokableClass('stringTypeSerializer', 'Zoop\Shard\Test\Serializer\TestAsset\StringSerializer');

        $this->documentManager = $manifest->getServiceManager()->get('objectmanager');
        $this->serializer = $manifest->getServiceManager()->get('serializer');
        $this->unserializer = $manifest->getServiceManager()->get('unserializer');
    }

    public function testSerializer()
    {
        $flavour = new Flavour('cherry');

        $array = $this->serializer->toArray($flavour, $this->documentManager);

        $this->assertEquals('Cherry', $array['name']);
    }

    public function testUnserializer()
    {
        $data = array(
            'name' => 'Cherry'
        );

        $flavour = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\Flavour'
        );

        $this->assertEquals('cherry', $flavour->getName());
    }
}
