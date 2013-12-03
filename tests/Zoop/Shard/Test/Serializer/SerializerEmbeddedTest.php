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

    public function testUnserializeUpdateEmbedMany()
    {
        //create the ACL group
        $admins = new AccessControl\Administrators();

        //create the users
        $adminName = 'Admin Name';
        $admin = new AccessControl\Administrator();
        $admin->setName($adminName);

        $super1Name = 'Super User 1 Name';
        $super1 = new AccessControl\SuperUser();
        $super1->setName($super1Name);

        $super2Name = 'Super User 2 Name';
        $super2 = new AccessControl\SuperUser();
        $super2->setName($super2Name);

        //add users to group and set the group owner
        $admins->setOwner($admin);
        $admins->addUser($super1);
        $admins->addUser($super2);

        //save the document first
        $this->documentManager->persist($admin);
        $this->documentManager->flush();

        //get the serialized data
        $data = $this->serializer->toArray($admins);

        //change the number of users in the group back to 1
        unset($data['users'][1]);

        /* @var $mergedDoc AccessControl\Administrators */
        $mergedDoc = $this->unserializer->fromArray(
                $data, 'Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl\Administrators', $admins, Unserializer::UNSERIALIZE_UPDATE
        );
        $this->assertTrue($mergedDoc instanceof AccessControl\Administrators);
        $this->assertCount(1, $mergedDoc->getUsers());

        $this->documentManager->remove($admins);
        $this->documentManager->flush();
    }

}
