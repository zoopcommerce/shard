<?php

namespace Zoop\Shard\Test\Validator;

use Zoop\Shard\Manifest;
use Zoop\Shard\Validator\Events;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Validator\TestAsset\Document\Country;
use Zoop\Shard\Test\Validator\TestAsset\Document\Group;
use Zoop\Shard\Test\Validator\TestAsset\Document\Profile;
use Zoop\Shard\Test\Validator\TestAsset\Document\User;

class ValidatorAssociationTest extends BaseTest
{

    protected $calls = [];

    public function setUp()
    {

        $manifest = new Manifest(
            [
                'models' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.validator' => true,
                    'extension.odmcore' => $this->getOdmCoreConfig()
                ],
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');

        $eventManager = $this->documentManager->getEventManager();
        $eventManager->addEventListener(Events::INVALID_MODEL, $this);

        $this->calls = [];

        $user = new User();
        $user->setUsername('josh');
        $profile = new Profile('crimson', 'ronin');
        $country = new Country('Australia');
        $profile->setCountry($country);
        $user->setProfile($profile);
        $user->addGroup(new Group('bce'));
        $user->addGroup(new Group('zoop'));

        $this->documentManager->persist($user);
        $this->documentManager->flush();

        $this->id = $user->getId();
        $this->documentManager->clear();
    }

    public function testValidUpdate()
    {
        $documentManager = $this->documentManager;
        $repository = $documentManager->getRepository('Zoop\Shard\Test\Validator\TestAsset\Document\User');
        $user = $repository->find($this->id);

        //do the valid update
        $user->setUsername('hsoj');
        $documentManager->flush();
        $this->assertFalse(isset($this->calls[Events::INVALID_MODEL]));

        $documentManager->clear();
        $user = $repository->find($this->id);

        $this->assertEquals('hsoj', $user->getUsername());
        $this->assertEquals('crimson', $user->getProfile()->getFirstname());
        $this->assertEquals('Australia', $user->getProfile()->getCountry()->getName());
        $this->assertCount(2, $user->getGroups());
    }

    public function testInvalidEmbedOneUpdate()
    {
        $documentManager = $this->documentManager;
        $repository = $documentManager->getRepository('Zoop\Shard\Test\Validator\TestAsset\Document\User');
        $user = $repository->find($this->id);

        //do the invalid update
        $user->setUsername(null);
        $user->setProfile(null);

        $documentManager->flush();
        $this->assertTrue(isset($this->calls[Events::INVALID_MODEL]));

        $documentManager->clear();
        $user = $repository->find($this->id);

        $this->assertEquals('josh', $user->getUsername());
        $this->assertEquals('crimson', $user->getProfile()->getFirstname());
        $this->assertEquals('Australia', $user->getProfile()->getCountry()->getName());
        $this->assertCount(2, $user->getGroups());
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
