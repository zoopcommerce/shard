<?php

namespace Zoop\Shard\Test\Serializer;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Apple;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Orange;

class SerializerDiscriminatorTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'documents' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.serializer' => true
                ],
                'document_manager' => 'testing.documentmanager',
                'service_manager_config' => [
                    'factories' => [
                        'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                    ]
                ]
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');
        $this->serializer = $manifest->getServiceManager()->get('serializer');
        $this->unserializer = $manifest->getServiceManager()->get('unserializer');
    }

    public function testSerializeDiscriminator()
    {
        $testDoc = new Apple();
        $testDoc->setVariety('granny smith');

        $correct = array(
            'type' => 'apple',
            'variety' => 'granny smith',
        );

        $array = $this->serializer->toArray($testDoc);

        $this->assertEquals($correct, $array);

        $testDoc = new Orange();
        $testDoc->setVariety('naval');

        $correct = array(
            'type' => 'orange',
            'variety' => 'naval',
        );

        $array = $this->serializer->toArray($testDoc);

        $this->assertEquals($correct, $array);
    }

    public function testUnserializeDiscriminator()
    {
        $data = array(
            'type' => 'apple',
            'variety' => 'red delicious',
            'color' => 'red'
        );

        $testDoc = $this->unserializer->fromArray(
            $data,
            $this->documentManager->getClassMetadata('Zoop\Shard\Test\Serializer\TestAsset\Document\Fruit')
        );

        $this->assertTrue($testDoc instanceof Apple);
        $this->assertEquals('red', $testDoc->getColor());
    }
}
