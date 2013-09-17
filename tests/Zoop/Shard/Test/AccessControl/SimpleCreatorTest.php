<?php

namespace Zoop\Shard\Test\AccessControl;

use Zoop\Shard\AccessControl\Events as AccessControlEvents;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\AccessControl\TestAsset\Document\Simple;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\TestAsset\RoleAwareUser;

class SimpleCreatorTest extends BaseTest
{
    protected $calls = array();

    public function setUp()
    {
        $manifest = new Manifest(
            [
                'model_map' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.accessControl' => true,
                    'extension.odmcore' => true
                ],
                'service_manager_config' => [
                    'factories' => [
                        'user' => function () {
                            $user = new RoleAwareUser();
                            $user->setUsername('toby');
                            $user->addRole('creator');

                            return $user;
                        }
                    ]
                ]
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
    }

    public function testCreateAllow()
    {
        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(AccessControlEvents::CREATE_DENIED, $this);

        $testDoc = new Simple();

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertNotNull($testDoc->getId());
        $this->assertFalse(isset($this->calls[AccessControlEvents::CREATE_DENIED]));
    }

    public function testUpdateDeny()
    {
        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(AccessControlEvents::UPDATE_DENIED, $this);

        $testDoc = new Simple();
        $testDoc->setName('lucy');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $testDoc->setName('changed');

        $documentManager->flush();

        $this->assertTrue(isset($this->calls[AccessControlEvents::UPDATE_DENIED]));
        $this->assertEquals('lucy', $testDoc->getName());
    }

    public function testDeleteDeny()
    {
        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(AccessControlEvents::DELETE_DENIED, $this);

        $testDoc = new Simple();
        $testDoc->setName('lucy');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $documentManager->remove($testDoc);
        $documentManager->flush();

        $this->assertTrue(isset($this->calls[AccessControlEvents::DELETE_DENIED]));
    }

    public function testReadDeny()
    {
        $documentManager = $this->documentManager;

        $toby = new Simple();
        $toby->setName('toby');
        $miriam = new Simple();
        $miriam->setName('miriam');
        $documentManager->persist($toby);
        $documentManager->persist($miriam);
        $documentManager->flush();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($toby));

        $toby = $repository->find($toby->getId());
        $this->assertNull($toby);
        $miriam = $repository->find($miriam->getId());
        $this->assertNull($miriam);
    }

    protected function getAllNames($repository)
    {
        $names = array();
        $documents = $repository->findAll();
        foreach ($documents as $document) {
            $names[] = $document->getName();
        }
        sort($names);

        return $names;
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
