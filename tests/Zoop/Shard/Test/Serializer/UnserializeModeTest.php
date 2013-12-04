<?php

namespace Zoop\Shard\Test\Serializer;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Serializer\Unserializer;
use Zoop\Shard\Test\Serializer\TestAsset\Document\User;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Group;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Profile;

class UnserializeModeTest extends BaseTest
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

    public function testUnserializePatch()
    {
        $documentManager = $this->documentManager;

        $user = new User();
        $user->setUsername('superdweebie');
        $user->setPassword('secret'); //uses Serialize Ignore annotation
        $user->defineLocation('here');
        $user->addGroup(new Group('groupA'));
        $user->addGroup(new Group('groupB'));
        $user->setProfile(new Profile('Tim', 'Roediger'));
        $user->setActive(true);

        $documentManager->persist($user);
        $documentManager->flush();

        $id = $user->getId();
        $data = $this->serializer->toArray($user);
        $groups = $data['groups'];
        $groups[] = [
            'name'=> 'groupC'
        ];
        $documentManager->clear();

        $updated = $this->unserializer->fromArray(
            [
                'id' => $id,
                'location' => 'there',
                'active' => false,
                'groups' => $groups
            ],
            'Zoop\Shard\Test\Serializer\TestAsset\Document\User'
        );

        $this->assertEquals('there', $updated->location());
        $this->assertEquals('superdweebie', $updated->getUsername());
        $this->assertEquals('Tim', $updated->getProfile()->getFirstName());
        $this->assertEquals(false, $updated->getActive());
        $this->assertCount(3, $updated->getGroups());

        $documentManager->remove($updated);
        $documentManager->flush();
        $documentManager->clear();
    }

    public function testUnserializeUpdate()
    {
        $documentManager = $this->documentManager;

        $user = new User();
        $user->setUsername('superdweebie');
        $user->setPassword('secret'); //uses Serialize Ignore annotation
        $user->defineLocation('here');
        $user->addGroup(new Group('groupA'));
        $user->addGroup(new Group('groupB'));
        $user->setProfile(new Profile('Tim', 'Roediger'));
        $user->setActive(true);

        $documentManager->persist($user);
        $documentManager->flush();
        $id = $user->getId();

        $data = $this->serializer->toArray($user);
        $groups = $data['groups'];
        unset($groups[1]);

        $documentManager->clear();

        /* @var $updated User */
        $updated = $this->unserializer->fromArray(
            [
                'id' => $id,
                'location' => 'there',
                'active' => false,
                'groups' => $groups
            ],
            'Zoop\Shard\Test\Serializer\TestAsset\Document\User',
            null,
            Unserializer::UNSERIALIZE_UPDATE
        );

        $this->assertEquals('there', $updated->location());
        $this->assertEquals(false, $updated->getActive());
        $this->assertEquals(null, $updated->getUsername());
        $this->assertEquals(null, $updated->getProfile());
        $this->assertCount(1, $updated->getGroups());
    }
}
