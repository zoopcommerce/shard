<?php

namespace Zoop\Shard\Test\Serializer;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Serializer\TestAsset\Document\User;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Group;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Profile;
use Zoop\Shard\Test\Serializer\TestAsset\Document\ClassName;

class SerializerTest extends BaseTest
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

        $this->serializer = $manifest->getServiceManager()->get('serializer');
        $this->unserializer = $manifest->getServiceManager()->get('unserializer');
    }

    public function testSerializer()
    {
        $user = new User();
        $user->setUsername('superdweebie');
        $user->setPassword('secret'); //uses Serialize Ignore annotation
        $user->defineLocation('here');
        $user->addGroup(new Group('groupA'));
        $user->addGroup(new Group('groupB'));
        $user->setProfile(new Profile('Tim', 'Roediger'));

        $correct = array(
            'username' => 'superdweebie',
            'location' => 'here',
            'groups' => array(
                array('name' => 'groupA'),
                array('name' => 'groupB'),
            ),
            'profile' => array(
                'firstname' => 'Tim',
                'lastname' => 'Roediger'
            ),
        );

        $array = $this->serializer->toArray($user);

        $this->assertEquals($correct, $array);
    }

    public function testApplySerializeMetadataToArray()
    {
        $array = array(
            'id' => null,
            'username' => 'superdweebie',
            'location' => 'here',
            'groups' => array(
                array('name' => 'groupA'),
                array('name' => 'groupB'),
            ),
            'password' => 'secret',
            'profile' => array(
                'firstname' => 'Tim',
                'lastname' => 'Roediger'
            ),
        );

        $correct = array(
            'id' => null,
            'username' => 'superdweebie',
            'location' => 'here',
            'groups' => array(
                array('name' => 'groupA'),
                array('name' => 'groupB'),
            ),
            'profile' => array(
                'firstname' => 'Tim',
                'lastname' => 'Roediger'
            ),
        );

        $array = $this->serializer->ApplySerializeMetadataToArray(
            $array,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\User'
        );

        $this->assertEquals($correct, $array);
    }

    public function testSerializeClassName()
    {
        $testDoc = new ClassName();
        $testDoc->setName('superdweebie');

        $correct = array(
            '_className' => 'Zoop\Shard\Test\Serializer\TestAsset\Document\ClassName',
            'name' => 'superdweebie',
        );

        $array = $this->serializer->toArray($testDoc);

        $this->assertEquals($correct, $array);
    }

    public function testUnserializer()
    {
        $data = array(
            'id' => 1234567890,
            'username' => 'superdweebie',
            'password' => 'testIgnore',
            'location' => 'here',
            'groups' => array(
                array('name' => 'groupA'),
                array('name' => 'groupB'),
            ),
            'profile' => array(
                'firstname' => 'Tim',
                'lastname' => 'Roediger'
            ),
        );

        $user = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\User'
        );

        $this->assertTrue($user instanceof User);
        $this->assertEquals(1234567890, $user->getId());
        $this->assertEquals('superdweebie', $user->getUsername());
        $this->assertEquals(null, $user->getPassword());
        $this->assertEquals('here', $user->location());
        $this->assertEquals('groupA', $user->getGroups()[0]->getName());
        $this->assertEquals('groupB', $user->getGroups()[1]->getName());
        $this->assertEquals('Tim', $user->getProfile()->getFirstname());
        $this->assertEquals('Roediger', $user->getProfile()->getLastname());
    }

    public function testUnserializeClassName()
    {
        $data = array(
            '_className' => 'Zoop\Shard\Test\Serializer\TestAsset\Document\ClassName',
            'id' => null,
            'name' => 'superdweebie',
        );

        $testDoc = $this->unserializer->fromArray($data);

        $this->assertTrue($testDoc instanceof ClassName);
        $this->assertEquals('superdweebie', $testDoc->getName());
    }
}
