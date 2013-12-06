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
    const USER_CLASS = 'Zoop\Shard\Test\Serializer\TestAsset\Document\User';
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

    public function testUnserializePatchGeneral()
    {
        $documentManager = $this->documentManager;

        $user = new User();
        $user->setUsername('superdweebie');
        $user->setPassword('secret'); //uses Serialize Ignore annotation
        $user->defineLocation('here');
        $user->setProfile(new Profile('Tim', 'Roediger'));
        $user->setActive(true);

        $documentManager->persist($user);
        $documentManager->flush();

        $id = $user->getId();
        $documentManager->clear();
        /* @var $updated User */
        $updated = $this->unserializer->fromArray(
            [
                'id' => $id,
                'location' => 'there',
                'profile' => [
                    'firstname' => 'Tom'
                ],
                'active' => false
            ],
            self::USER_CLASS,
            $user
        );

        $this->assertEquals('there', $updated->location());
        $this->assertEquals('superdweebie', $updated->getUsername());
        $this->assertEquals('Tom', $updated->getProfile()->getFirstname());
        $this->assertEquals(false, $updated->getActive());
    }

    public function testUnserializePatchAddItemToCollection()
    {
        $documentManager = $this->documentManager;

        $user = new User();
        $user->addGroup(new Group('groupA'));
        $user->addGroup(new Group('groupB'));

        $documentManager->persist($user);
        $documentManager->flush();

        $id = $user->getId();
        $data = $this->serializer->toArray($user);
        $groups = $data['groups'];
        $groups[] = [
            'name'=> 'groupC'
        ];

        /* @var $updated User */
        $updated = $this->unserializer->fromArray(
            [
                'id' => $id,
                'groups' => $groups
            ],
            self::USER_CLASS,
            $user
        );

        // this works pre flush
        $userGroups = $updated->getGroups();
        $this->assertCount(3, $userGroups);
        $this->assertEquals('groupA', $userGroups[0]->getName());
        $this->assertEquals('groupB', $userGroups[1]->getName());
        $this->assertEquals('groupC', $userGroups[2]->getName());

        $documentManager->flush();
        $documentManager->clear();

        $userFind = $documentManager->find(self::USER_CLASS, $id);

        //this fails from DB
        $userGroups = $userFind->getGroups();
        $this->assertCount(3, $userGroups);
        $this->assertEquals('groupA', $userGroups[0]->getName());
        $this->assertEquals('groupB', $userGroups[1]->getName());
        $this->assertEquals('groupC', $userGroups[2]->getName());
    }

    public function testUnserializePatchRemoveItemFromCollection()
    {
        $documentManager = $this->documentManager;

        $user = new User();
        $user->addGroup(new Group('groupA'));
        $user->addGroup(new Group('groupB'));

        $documentManager->persist($user);
        $documentManager->flush();

        $id = $user->getId();
        $data = $this->serializer->toArray($user);
        $groups = $data['groups'];
        unset($groups[1]);

        /* @var $updated User */
        $updated = $this->unserializer->fromArray(
            [
                'id' => $id,
                'groups' => $groups
            ],
            self::USER_CLASS,
            $user
        );

        $userGroups = $updated->getGroups();
        $this->assertCount(1, $userGroups);
        $this->assertEquals('groupA', $userGroups[0]->getName());

        $documentManager->flush();
        $documentManager->clear();

        $userFind = $documentManager->find(self::USER_CLASS, $id);
        $userGroups = $userFind->getGroups();
        $this->assertCount(1, $userGroups);
        $this->assertEquals('groupA', $userGroups[0]->getName());
    }

    public function testUnserializePatchEmptyCollection()
    {
        $documentManager = $this->documentManager;

        $user = new User();
        $user->addGroup(new Group('groupA'));
        $user->addGroup(new Group('groupB'));

        $documentManager->persist($user);
        $documentManager->flush();

        $id = $user->getId();

        /* @var $updated User */
        $updated = $this->unserializer->fromArray(
            [
                'id' => $id,
                'groups' => []
            ],
            self::USER_CLASS,
            $user
        );
        $this->assertCount(0, $updated->getGroups());

        $documentManager->flush();
        $documentManager->clear();

        $userFind = $documentManager->find(self::USER_CLASS, $id);
        $this->assertCount(0, $userFind->getGroups());
    }

    public function testUnserializeUpdateGeneral()
    {
        $documentManager = $this->documentManager;

        $user = new User();
        $user->setUsername('superdweebie');
        $user->setPassword('secret'); //uses Serialize Ignore annotation
        $user->defineLocation('here');
        $user->setProfile(new Profile('Tim', 'Roediger'));
        $user->setActive(true);

        $documentManager->persist($user);
        $documentManager->flush();
        $id = $user->getId();
        $documentManager->clear();

        /* @var $updated User */
        $updated = $this->unserializer->fromArray(
            [
                'id' => $id,
                'location' => 'there',
                'active' => false,
                'profile' => [
                    'firstname' => 'Tom'
                ]
            ],
            self::USER_CLASS,
            $user,
            Unserializer::UNSERIALIZE_UPDATE
        );

        $this->assertEquals('there', $updated->location());
        $this->assertEquals(false, $updated->getActive());
        $this->assertEquals(null, $updated->getUsername());
        $this->assertEquals('Tom', $updated->getProfile()->getFirstname());
        $this->assertEquals(null, $updated->getProfile()->getLastname());
    }

    public function testUnserializeUpdateAddItemToCollection()
    {
        $documentManager = $this->documentManager;

        $user = new User();
        $user->addGroup(new Group('groupA'));
        $user->addGroup(new Group('groupB'));

        $documentManager->persist($user);
        $documentManager->flush();
        $id = $user->getId();
        $data = $this->serializer->toArray($user);
        $groups = $data['groups'];
        $groups[] = [
            'name'=> 'groupC'
        ];

        /* @var $updated User */
        $updated = $this->unserializer->fromArray(
            [
                'id' => $id,
                'groups' => $groups
            ],
            self::USER_CLASS,
            $user,
            Unserializer::UNSERIALIZE_UPDATE
        );

        $userGroups = $updated->getGroups();
        $this->assertCount(3, $userGroups);
        $this->assertEquals('groupA', $userGroups[0]->getName());
        $this->assertEquals('groupB', $userGroups[1]->getName());
        $this->assertEquals('groupC', $userGroups[2]->getName());

        $documentManager->flush();
        $documentManager->clear();

        $userFind = $documentManager->find(self::USER_CLASS, $id);
        $userGroups = $userFind->getGroups();
        $this->assertCount(3, $userGroups);
        $this->assertEquals('groupA', $userGroups[0]->getName());
        $this->assertEquals('groupB', $userGroups[1]->getName());
        $this->assertEquals('groupC', $userGroups[2]->getName());
    }

    public function testUnserializeUpdateRemoveItemFromCollection()
    {
        $documentManager = $this->documentManager;

        $user = new User();
        $user->addGroup(new Group('groupA'));
        $user->addGroup(new Group('groupB'));

        $documentManager->persist($user);
        $documentManager->flush();
        $id = $user->getId();
        $data = $this->serializer->toArray($user);
        $groups = $data['groups'];
        unset($groups[0]);

        /* @var $updated User */
        $updated = $this->unserializer->fromArray(
            [
                'id' => $id,
                'groups' => $groups
            ],
            self::USER_CLASS,
            $user,
            Unserializer::UNSERIALIZE_UPDATE
        );

        $userGroups = $updated->getGroups();
        $this->assertCount(1, $userGroups);
        $this->assertEquals('groupB', $userGroups[0]->getName());

        $documentManager->flush();
        $documentManager->clear();

        $userFind = $documentManager->find(self::USER_CLASS, $id);
        $userGroups = $userFind->getGroups();
        $this->assertCount(1, $userGroups);
        $this->assertEquals('groupB', $userGroups[0]->getName());
    }

    public function testUnserializeUpdateEmptyCollection()
    {
        $documentManager = $this->documentManager;

        $user = new User();
        $user->addGroup(new Group('groupA'));
        $user->addGroup(new Group('groupB'));

        $documentManager->persist($user);
        $documentManager->flush();
        $id = $user->getId();

        /* @var $updated User */
        $updated = $this->unserializer->fromArray(
            [
                'id' => $id,
                'groups' => []
            ],
            self::USER_CLASS,
            $user,
            Unserializer::UNSERIALIZE_UPDATE
        );

        $this->assertCount(0, $updated->getGroups());

        $documentManager->flush();
        $documentManager->clear();

        $userFind = $documentManager->find(self::USER_CLASS, $id);
        $this->assertCount(0, $userFind->getGroups());
    }
}
