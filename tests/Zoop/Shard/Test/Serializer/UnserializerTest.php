<?php

namespace Zoop\Shard\Test\Serializer;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Serializer\TestAsset\Document\User;

class UnserializerTest extends BaseTest
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
                    'extension.odmcore' => $this->getOdmCoreConfig()
                ],
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
        $this->unserializer = $manifest->getServiceManager()->get('unserializer');
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
}
