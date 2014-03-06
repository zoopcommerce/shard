<?php

namespace Zoop\Shard\Test\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl;
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
        $manifest = new Manifest([
            'models' => [
                __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
            ],
            'extension_configs' => [
                'extension.serializer' => true,
                'extension.odmcore' => $this->getOdmCoreConfig()
            ],
        ]);

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
                    '_doctrine_class_name' => 'apple',
                    'variety' => 'red delicious',
                    'color' => 'red'
                ],
                [
                    '_doctrine_class_name' => 'orange',
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
                    '_doctrine_class_name' => 'apple',
                    'variety' => 'granny smith',
                    'type' => 'apple'
                ],
                [
                    '_doctrine_class_name' => 'orange',
                    'variety' => 'naval',
                    'type' => 'orange'
                ]
            ]
        );

        $array = $this->serializer->toArray($fruitBowl);

        $this->assertEquals($correct, $array);
    }

    public function testSerializeSingleEmbeddedNoDiscriminator()
    {
        $adminName = 'Admin Name';
        $admin = new AccessControl\Administrator();
        $admin->setName($adminName);

        $super1Name = 'Super User 1 Name';
        $super1 = new AccessControl\SuperUser();
        $super1->setName($super1Name);

        $super2Name = 'Super User 2 Name';
        $super2 = new AccessControl\SuperUser();
        $super2->setName($super2Name);

        $admins = new AccessControl\Administrators();
        $admins->setOwner($admin);

        $admins->addUser($super1);
        $admins->addUser($super2);

        $correct = array(
            'owner' => [
                '_doctrine_class_name' => 'Administrator',
                'name' => $adminName
            ],
            'users' => [
                [
                    '_doctrine_class_name' => 'SuperUser',
                    'name' => $super1Name
                ],
                [
                    '_doctrine_class_name' => 'SuperUser',
                    'name' => $super2Name
                ]
            ],
        );

        $array = $this->serializer->toArray($admins);
        $this->assertEquals($correct, $array);
    }

    public function testUnserializeSingleEmbeddedNoDiscriminator()
    {
        $adminName = 'Admin Name';
        $super1Name = 'Super User 1 Name';
        $super2Name = 'Super User 2 Name';

        $data = array(
            'owner' => [
                '_doctrine_class_name' => 'Administrator',
                'name' => $adminName
            ],
            'users' => [
                [
                    '_doctrine_class_name' => 'SuperUser',
                    'name' => $super1Name
                ],
                [
                    '_doctrine_class_name' => 'SuperUser',
                    'name' => $super2Name
                ]
            ],
        );

        $testDoc = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl\Administrators'
        );

        $this->assertTrue($testDoc instanceof AccessControl\Administrators);
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

    public function testUnserializeReferenceDiscriminatorWithRefNotation()
    {
        $apple = new Apple;
        $apple->setColor('green');
        $this->documentManager->persist($apple);

        $orange = new Orange;
        $orange->setSize('small');
        $this->documentManager->persist($orange);

        $this->documentManager->flush();

        $data = array(
            'referencedFruit' => [
                ['$ref' => $apple->getId(), 'type' => 'apple'],
                ['$ref' => $orange->getId(), 'type' => 'orange']
            ]
        );

        $testDoc = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\FruitBowl'
        );

        $this->assertTrue($testDoc instanceof FruitBowl);
        $this->assertCount(2, $testDoc->getReferencedFruit());

        $item1 = $testDoc->getReferencedFruit()[0];
        $this->assertTrue($item1 === $apple);

        $item2 = $testDoc->getReferencedFruit()[1];
        $this->assertTrue($item2 === $orange);

        $this->documentManager->remove($apple);
        $this->documentManager->remove($orange);
        $this->documentManager->flush();
    }

    public function testUnserializeSingleReferenceDiscriminatorWithRefNotation()
    {
        $apple = new Apple;
        $apple->setColor('green');
        $this->documentManager->persist($apple);

        $this->documentManager->flush();

        $data = array(
            'referencedSingleFruit' => ['$ref' => $apple->getId(), 'type' => 'apple']
        );

        $testDoc = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\FruitBowl'
        );

        $this->assertTrue($testDoc instanceof FruitBowl);

        $this->assertTrue($apple === $testDoc->getReferencedSingleFruit());

        $this->documentManager->remove($apple);
        $this->documentManager->flush();
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
