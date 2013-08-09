<?php

namespace Zoop\Shard\Test\State;

use Zoop\Shard\Manifest;
use Zoop\Shard\State\Events;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\State\TestAsset\Document\AccessControlled;
use Zoop\Shard\Test\TestAsset\RoleAwareUser;

class AccessControlGlobTest extends BaseTest {

    protected $calls = array();

    public function setUp(){

        $manifest = new Manifest([
            'documents' => [
                __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
            ],
            'extension_configs' => [
                'extension.state' => true,
                'extension.accessControl' => true
            ],
            'document_manager' => 'testing.documentmanager',
            'service_manager_config' => [
                'factories' => [
                    'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                    'user' => function(){
                        $user = new RoleAwareUser();
                        $user->setUsername('toby');
                        $user->addRole('glob');
                        return $user;
                    }
                ]
            ]
        ]);

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');
    }

    public function testCreateDeny(){

        $this->calls = array();

        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener('createDenied', $this);

        $testDoc = new AccessControlled();

        $testDoc->setName('deny');
        $testDoc->setState('published');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertNull($testDoc->getId());
        $this->assertTrue(isset($this->calls['createDenied']));
    }

    public function testTransitionAllow(){

        $this->calls = array();

        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::transitionDenied, $this);

        $testDoc = new AccessControlled();

        $testDoc->setName('version 1');
        $testDoc->setState('draft');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $testDoc->setState('review');

        $documentManager->flush();

        $this->assertEquals('review', $testDoc->getState());
        $this->assertFalse(isset($this->calls[Events::transitionDenied]));
    }

    public function testTransitionAllow2(){

        $this->calls = array();

        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::transitionDenied, $this);

        $testDoc = new AccessControlled();

        $testDoc->setName('nice doc');
        $testDoc->setState('draft');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $testDoc->setState('published');

        $documentManager->flush();

        $this->assertEquals('published', $testDoc->getState());
        $this->assertFalse(isset($this->calls[Events::transitionDenied]));
    }

    public function testTransitionDeny(){

        $this->calls = array();

        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::transitionDenied, $this);

        $testDoc = new AccessControlled();

        $testDoc->setName('nice doc');
        $testDoc->setState('review');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertEquals('review', $testDoc->getState());

        $testDoc->setState('published');

        $documentManager->flush();

        $this->assertEquals('review', $testDoc->getState());
        $this->assertTrue(isset($this->calls[Events::transitionDenied]));
    }

    public function __call($name, $arguments){
        $this->calls[$name] = $arguments;
    }
}