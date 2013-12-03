<?php

namespace Zoop\Shard\Test\Serializer;

use Zoop\Shard\Manifest;
use Zoop\Shard\Serializer\Unserializer;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl;
use Zoop\Shard\Test\Serializer\TestAsset\Document\EmbeddedApple;
use Zoop\Shard\Test\Serializer\TestAsset\Document\FruitBowl;

class SerializerEmbeddedTest extends BaseTest
{

    public function setUp()
    {
        $manifest = new Manifest(
                [
            'models' => [
                __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
            ],
            'extension_configs' => [
                'extension.serializer' => true,
                'extension.odmcore' => true
            ],
                ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
        $this->serializer = $manifest->getServiceManager()->get('serializer');
        $this->unserializer = $manifest->getServiceManager()->get('unserializer');
    }

    public function testSerializeEmbedOneTargetDocument()
    {

        $apple = new EmbeddedApple();
        $apple->setColor('red');

        $fruitBowl = new FruitBowl;
        $fruitBowl->setEmbeddedSingleFruitTarget($apple);

        $correct = [
            'embeddedSingleFruitTarget' => [
                'color' => 'red'
            ]
        ];

        $array = $this->serializer->toArray($fruitBowl);
        $this->assertEquals($correct, $array);
    }

    public function testUnserializeEmbedOneTargetDocument()
    {
        $data = [
            'embeddedSingleFruitTarget' => [
                '_doctrine_class_name' => 'apple',
                'color' => 'red'
            ]
        ];

        /* @var $testDoc FruitBowl */
        $testDoc = $this->unserializer->fromArray(
                $data, 'Zoop\Shard\Test\Serializer\TestAsset\Document\FruitBowl'
        );

        $this->assertTrue($testDoc instanceof FruitBowl);
        $this->assertTrue($testDoc->getEmbeddedSingleFruitTarget() instanceof EmbeddedApple);
    }

    public function testSerializeEmbedOneDiscriminatorMap()
    {

        $apple = new EmbeddedApple();
        $apple->setColor('red');

        $fruitBowl = new FruitBowl;
        $fruitBowl->setEmbeddedSingleFruit($apple);

        $correct = [
            'embeddedSingleFruit' => [
                '_doctrine_class_name' => 'apple',
                'color' => 'red'
            ]
        ];

        $array = $this->serializer->toArray($fruitBowl);

        $this->assertEquals($correct, $array);
    }

    public function testUnserializeEmbedOneDiscriminatorMap()
    {
        $data = [
            'embeddedSingleFruit' => [
                '_doctrine_class_name' => 'apple',
                'color' => 'red'
            ]
        ];

        /* @var $testDoc FruitBowl */
        $testDoc = $this->unserializer->fromArray(
                $data, 'Zoop\Shard\Test\Serializer\TestAsset\Document\FruitBowl'
        );

        $this->assertTrue($testDoc instanceof FruitBowl);
        $this->assertTrue($testDoc->getEmbeddedSingleFruit() instanceof EmbeddedApple);
    }

}
