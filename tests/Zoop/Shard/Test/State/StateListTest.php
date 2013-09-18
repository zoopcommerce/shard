<?php

namespace Zoop\Shard\Test\State;

use Zoop\Shard\Manifest;
use Zoop\Shard\State\Events;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\State\TestAsset\Document\StateList;

class StateListTest extends BaseTest
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
                    'extension.odmcore' => true
                ],
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
        $eventManager = $this->documentManager->getEventManager();
        $eventManager->addEventListener(Events::BAD_STATE, $this);
    }

    public function testCreateWithStateOnListFunction()
    {
        $this->calls = [];
        $documentManager = $this->documentManager;

        $testDoc = new StateList();

        $testDoc->setName('doc 1');
        $testDoc->setState('active');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertNotNull($testDoc->getId());
        $this->assertFalse(isset($this->calls[Events::BAD_STATE]));
    }

    public function testCreateWithStateNotOnListFunction()
    {
        $this->calls = [];
        $documentManager = $this->documentManager;

        $testDoc = new StateList();

        $testDoc->setName('doc 1');
        $testDoc->setState('bad state');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertNull($testDoc->getId());
        $this->assertTrue(isset($this->calls[Events::BAD_STATE]));
    }

    public function testUpdateWithStateOnListFunction()
    {
        $this->calls = [];
        $documentManager = $this->documentManager;

        $testDoc = new StateList();

        $testDoc->setName('doc 1');
        $testDoc->setState('active');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertFalse(isset($this->calls[Events::BAD_STATE]));

        $testDoc->setState('inactive');

        $documentManager->flush();

        $this->assertNotNull($testDoc->getId());
        $this->assertFalse(isset($this->calls[Events::BAD_STATE]));
    }

    public function testUpdateWithStateNotOnListFunction()
    {
        $this->calls = [];
        $documentManager = $this->documentManager;

        $testDoc = new StateList();

        $testDoc->setName('doc 1');
        $testDoc->setState('active');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertFalse(isset($this->calls[Events::BAD_STATE]));

        $testDoc->setState('bad state');

        $documentManager->flush();

        $this->assertNotNull($testDoc->getId());
        $this->assertTrue(isset($this->calls[Events::BAD_STATE]));
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
