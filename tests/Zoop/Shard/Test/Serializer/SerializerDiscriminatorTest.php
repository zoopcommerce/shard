<?php

namespace Zoop\Shard\Test\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Apple;
use Zoop\Shard\Test\Serializer\TestAsset\Document\FruitBowl;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Orange;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Family;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Male;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Female;

class SerializerDiscriminatorTest extends BaseTest
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
            'Zoop\Shard\Test\Serializer\TestAsset\Document\Fruit'
        );

        $this->assertTrue($testDoc instanceof Apple);
        $this->assertEquals('red', $testDoc->getColor());
    }

    public function testUnserializeEmbeddedDiscriminator()
    {
        $data = array(
            'embeddedFruit' => [
                [
                    'type' => 'apple',
                    'variety' => 'red delicious',
                    'color' => 'red'
                ],
                [
                    'type' => 'orange',
                    'variety' => 'naval',
                    'size' => 'small'
                ]
            ]
        );

        $testDoc = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\FruitBowl'
        );

        $this->assertTrue($testDoc instanceof FruitBowl);
        $this->assertCount(2, $testDoc->getEmbeddedFruit());
    }

    public function testSerializeEmbeddedDiscriminator()
    {

        $apple = new Apple();
        $apple->setVariety('granny smith');
        $orange = new Orange();
        $orange->setVariety('naval');

        $fruitBowl = new FruitBowl;
        $fruitBowl->setEmbeddedFruit(new ArrayCollection([$apple, $orange]));

        $correct = array(
            'embeddedFruit' => [
                [
                    'type' => 'apple',
                    'variety' => 'granny smith'
                ],
                [
                    'type' => 'orange',
                    'variety' => 'naval'
                ]
            ]
        );

        $array = $this->serializer->toArray($fruitBowl);

        $this->assertEquals($correct, $array);
    }

    public function testUnserializeReferenceDiscriminator()
    {
        $data = array(
            'referencedFruit' => [
                [
                    'type' => 'apple',
                    'variety' => 'red delicious',
                    'color' => 'red'
                ],
                [
                    'type' => 'orange',
                    'variety' => 'naval',
                    'size' => 'small'
                ]
            ]
        );

        $testDoc = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\FruitBowl'
        );

        $this->assertTrue($testDoc instanceof FruitBowl);
        $this->assertCount(2, $testDoc->getReferencedFruit());
    }

    public function testSerializeReferenceDiscriminator()
    {

        $apple = new Apple();
        $apple->setVariety('granny smith');
        $orange = new Orange();
        $orange->setVariety('naval');

        $fruitBowl = new FruitBowl;
        $fruitBowl->setReferencedFruit(new ArrayCollection([$apple, $orange]));

        $this->documentManager->persist($fruitBowl);
        $this->documentManager->flush();

        $correct = array(
            'id' => $fruitBowl->getId(),
            'referencedFruit' => [
                [
                    'id' => $fruitBowl->getReferencedFruit()[0]->getId(),
                    'type' => 'apple',
                    'variety' => 'granny smith'
                ],
                [
                    'id' => $fruitBowl->getReferencedFruit()[1]->getId(),
                    'type' => 'orange',
                    'variety' => 'naval'
                ]
            ]
        );

        $array = $this->serializer->toArray($fruitBowl);

        $this->assertEquals($correct, $array);
    }

    public function testUnserializeEmbeddedDiscriminatorSetStrategy()
    {
        $data = array(
            'parents' => [
                'homer' => [
                    'gender' => 'male',
                    'age' => 40,
                ],
                'marge' => [
                    'gender' => 'female',
                    'age' => 38
                ]
            ]
        );

        $testDoc = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\Family'
        );

        $this->assertTrue($testDoc instanceof Family);
        $this->assertCount(2, $testDoc->getParents());
    }

    public function testSerializeEmbeddedDiscriminatorSetStrategy()
    {

        $dad = new Male();
        $dad->setName('homer');
        $dad->setAge(40);

        $mum = new Female();
        $mum->setName('marge');
        $mum->setAge(38);

        $family = new Family;
        $family->setParents(new ArrayCollection([$dad, $mum]));

        $correct = array(
            'parents' => [
                'homer' => [
                    'gender' => 'male',
                    'age' => 40,
                ],
                'marge' => [
                    'gender' => 'female',
                    'age' => 38
                ]
            ]
        );

        $array = $this->serializer->toArray($family);

        $this->assertEquals($correct, $array);
    }

    public function testUnserializeReferencedDiscriminatorSetStrategy()
    {
        $data = array(
            'kids' => [
                'bart' => [
                    'gender' => 'male',
                    'age' => 8,
                ],
                'lisa' => [
                    'gender' => 'female',
                    'age' => 6
                ],
                'maggie' => [
                    'gender' => 'female',
                    'age' => 1
                ]
            ]
        );

        $testDoc = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\Family'
        );

        $this->assertTrue($testDoc instanceof Family);
        $this->assertCount(3, $testDoc->getKids());
    }

    public function testSerializeReferencedDiscriminatorSetStrategy()
    {

        $bart = new Male();
        $bart->setName('bart');
        $bart->setAge(8);

        $lisa = new Female();
        $lisa->setName('lisa');
        $lisa->setAge(6);

        $maggie = new Female();
        $maggie->setName('maggie');
        $maggie->setAge(1);

        $family = new Family;
        $family->setKids(new ArrayCollection([$bart, $lisa, $maggie]));

        $correct = array(
            'kids' => [
                'bart' => [
                    'gender' => 'male',
                    'age' => 8,
                ],
                'lisa' => [
                    'gender' => 'female',
                    'age' => 6
                ],
                'maggie' => [
                    'gender' => 'female',
                    'age' => 1
                ]
            ]
        );

        $array = $this->serializer->toArray($family);

        $this->assertEquals($correct, $array);
    }
}
