<?php

namespace Zoop\Shard\Test\State;

use Zoop\Shard\Manifest;
use Zoop\Shard\State\Events;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\State\TestAsset\Document\StateList;

class StateListTest extends BaseTest {

    protected $calls = array();

    public function setUp(){

        $manifest = new Manifest([
            'documents' => [
                __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
            ],
            'extension_configs' => [
                'extension.state' => true
            ],
            'document_manager' => 'testing.documentmanager',
            'service_manager_config' => [
                'factories' => [
                    'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                ]
            ]
        ]);

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');
        $eventManager = $this->documentManager->getEventManager();
        $eventManager->addEventListener(Events::badState, $this);
    }

    public function testCreateWithStateOnListFunction(){

        $this->calls = [];
        $documentManager = $this->documentManager;

        $testDoc = new StateList();

        $testDoc->setName('doc 1');
        $testDoc->setState('active');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertNotNull($testDoc->getId());
        $this->assertFalse(isset($this->calls[Events::badState]));
    }

    public function testCreateWithStateNotOnListFunction() {

        $this->calls = [];
        $documentManager = $this->documentManager;

        $testDoc = new StateList();

        $testDoc->setName('doc 1');
        $testDoc->setState('bad state');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertNull($testDoc->getId());
        $this->assertTrue(isset($this->calls[Events::badState]));
    }

    public function testUpdateWithStateOnListFunction(){

        $this->calls = [];
        $documentManager = $this->documentManager;

        $testDoc = new StateList();

        $testDoc->setName('doc 1');
        $testDoc->setState('active');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertFalse(isset($this->calls[Events::badState]));

        $testDoc->setState('inactive');

        $documentManager->flush();

        $this->assertNotNull($testDoc->getId());
        $this->assertFalse(isset($this->calls[Events::badState]));
    }

    public function testUpdateWithStateNotOnListFunction() {

        $this->calls = [];
        $documentManager = $this->documentManager;

        $testDoc = new StateList();

        $testDoc->setName('doc 1');
        $testDoc->setState('active');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertFalse(isset($this->calls[Events::badState]));

        $testDoc->setState('bad state');

        $documentManager->flush();

        $this->assertNotNull($testDoc->getId());
        $this->assertTrue(isset($this->calls[Events::badState]));
    }

    public function __call($name, $arguments){
        $this->calls[$name] = $arguments;
    }
}