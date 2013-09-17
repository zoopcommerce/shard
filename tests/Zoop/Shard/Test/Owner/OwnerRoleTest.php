<?php

namespace Zoop\Shard\Test\Owner;

use Zoop\Shard\AccessControl\Events as Events;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\Owner\TestAsset\Document\OwnerDoc;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\TestAsset\RoleAwareUser;

class OwnerRoleTest extends BaseTest
{
    protected $calls = array();

    public function setUp()
    {
        $manifest = new Manifest(
            [
                'object_map' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.accesscontrol' => true,
                    'extension.owner' => true,
                    'extension.odmcore' => true
                ],
                'service_manager_config' => [
                    'factories' => [
                        'user' => function () {
                            $user = new RoleAwareUser();
                            $user->setUsername('toby');

                            return $user;
                        }
                    ]
                ]
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('objectmanager');
    }

    public function testOwnerAllow()
    {
        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::UPDATE_DENIED, $this);

        $testDoc = new OwnerDoc();
        $testDoc->setName('my test');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertEquals('my test', $testDoc->getName());
        $id = $testDoc->getId();

        $documentManager->clear();
        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $testDoc->setName('different name');
        $documentManager->flush();

        $this->assertEquals('different name', $testDoc->getName());
        $this->assertFalse(isset($this->calls[Events::UPDATE_DENIED]));
    }

    public function testOwnerDeny()
    {
        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::UPDATE_DENIED, $this);

        $testDoc = new OwnerDoc();
        $testDoc->setName('my test');
        $testDoc->setOwner('bobby');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertEquals('my test', $testDoc->getName());
        $id = $testDoc->getId();

        $documentManager->clear();
        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $testDoc->setName('different name');
        $documentManager->flush();

        $this->assertEquals('my test', $testDoc->getName());
        $this->assertTrue(isset($this->calls[Events::UPDATE_DENIED]));
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
