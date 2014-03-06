<?php

namespace Zoop\Shard\Test\State;

use Zoop\Shard\Manifest;
use Zoop\Shard\AccessControl\Events as AccessControlEvents;
use Zoop\Shard\State\Events;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\State\TestAsset\Document\AccessControlled;
use Zoop\Shard\Test\TestAsset\RoleAwareUser;

class AccessControlWriterTest extends BaseTest
{
    protected $calls = array();

    public function setUp()
    {
        $manifest = new Manifest(
            [
                'models' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.state' => true,
                    'extension.accessControl' => true,
                    'extension.odmcore' => $this->getOdmCoreConfig()
                ],
                'service_manager_config' => [
                    'factories' => [
                        'user' => function () {
                            $user = new RoleAwareUser();
                            $user->setUsername('toby');
                            $user->addRole('writer');

                            return $user;
                        }
                    ]
                ]
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
    }

    public function testCreateDeny()
    {
        $this->calls = array();

        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(AccessControlEvents::CREATE_DENIED, $this);

        $testDoc = new AccessControlled();

        $testDoc->setName('deny');
        $testDoc->setState('published');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertNull($testDoc->getId());
        $this->assertTrue(isset($this->calls[AccessControlEvents::CREATE_DENIED]));
    }

    public function testTransitionAllow()
    {
        $this->calls = array();

        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::TRANSITION_DENIED, $this);

        $testDoc = new AccessControlled();

        $testDoc->setName('version 1');
        $testDoc->setState('draft');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $testDoc->setState('review');

        $documentManager->flush();

        $this->assertEquals('review', $testDoc->getState());
        $this->assertFalse(isset($this->calls[Events::TRANSITION_DENIED]));
    }

    public function testTransitionDeny()
    {
        $this->calls = array();

        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::TRANSITION_DENIED, $this);

        $testDoc = new AccessControlled();

        $testDoc->setName('nice doc');
        $testDoc->setState('draft');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $testDoc->setState('published');

        $documentManager->flush();

        $this->assertEquals('draft', $testDoc->getState());
        $this->assertTrue(isset($this->calls[Events::TRANSITION_DENIED]));
    }

    public function testTransitionDeny2()
    {
        $this->calls = array();

        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::TRANSITION_DENIED, $this);

        $testDoc = new AccessControlled();

        $testDoc->setName('nice doc');
        $testDoc->setState('draft');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $testDoc->setState('review');
        $documentManager->flush();

        $testDoc->setState('published');
        $documentManager->flush();

        $this->assertEquals('review', $testDoc->getState());
        $this->assertTrue(isset($this->calls[Events::TRANSITION_DENIED]));
    }

    public function testReadAccess()
    {
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $testDoc = new AccessControlled();

        $testDoc->setName('read doc');
        $testDoc->setState('draft');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $documentManager->clear();

        $testDoc = $documentManager->getRepository(get_class($testDoc))->find($testDoc->getId());
        $this->assertNotNull($testDoc);

        $testDoc->setState('review');
        $documentManager->flush();
        $documentManager->clear();

        $testDoc = $documentManager->getRepository(get_class($testDoc))->find($testDoc->getId());
        $this->assertNull($testDoc);
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
