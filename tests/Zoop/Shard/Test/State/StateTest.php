<?php

namespace Zoop\Shard\Test\State;

use Zoop\Shard\Manifest;
use Zoop\Shard\State\Events;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\State\TestAsset\Document\Simple;
use Zoop\Shard\Test\State\TestAsset\Subscriber;

class StateTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'model_map' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.state' => true,
                    'extension.odmcore' => true
                ],
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
        $this->extension = $manifest->getServiceManager()->get('extension.state');
    }

    public function testBasicFunction()
    {
        $documentManager = $this->documentManager;
        $testDoc = new Simple();

        $testDoc->setName('version 1');
        $testDoc->setState('state1');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertEquals('state1', $testDoc->getState());

        $testDoc->setState('state2');

        $documentManager->flush();
        $documentManager->clear();
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertEquals('state2', $testDoc->getState());
    }

    public function testFilter()
    {
        $documentManager = $this->documentManager;

        $testDocA = new Simple();
        $testDocA->setName('miriam');
        $testDocA->setState('active');

        $testDocB = new Simple();
        $testDocB->setName('lucy');
        $testDocB->setState('inactive');

        $documentManager->persist($testDocA);
        $documentManager->persist($testDocB);
        $documentManager->flush();
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('lucy', 'miriam'), $docNames);

        $documentManager->flush();
        $documentManager->clear();

        $this->extension->setReadFilterInclude(['active']);

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('miriam'), $docNames);

        $this->extension->setReadFilterInclude();
        $this->extension->setReadFilterExclude(['active']);

        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('lucy'), $docNames);

        $this->extension->setReadFilterInclude();
        $this->extension->setReadFilterExclude();

        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('lucy', 'miriam'), $docNames);
    }

    protected function getTestDocs()
    {
        $repository = $this->documentManager->getRepository('Zoop\Shard\Test\State\TestAsset\Document\Simple');
        $testDocs = $repository->findAll();
        $returnDocs = array();
        $returnNames = array();
        foreach ($testDocs as $testDoc) {
            $returnDocs[] = $testDoc;
            $returnNames[] = $testDoc->getName();
        }
        sort($returnNames);

        return array($returnDocs, $returnNames);
    }

    public function testEvents()
    {
        $subscriber = new Subscriber();

        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();
        $eventManager->addEventSubscriber($subscriber);

        $testDoc = new Simple();
        $testDoc->setName('version 1');
        $testDoc->setState('state1');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();
        $documentManager->clear();

        $calls = $subscriber->getCalls();
        $this->assertFalse(isset($calls[Events::PRE_TRANSITION]));
        $this->assertFalse(isset($calls[Events::POST_TRANSITION]));

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $testDoc->setState('state2');
        $subscriber->reset();

        $documentManager->flush();

        $calls = $subscriber->getCalls();
        $this->assertTrue(isset($calls[Events::PRE_TRANSITION]));
        $this->assertTrue(isset($calls[Events::POST_TRANSITION]));

        $documentManager->clear();
        $testDoc = $repository->find($id);

        $testDoc->setState('state3');
        $subscriber->reset();
        $subscriber->setRollbackTransition(true);

        $documentManager->flush();

        $calls = $subscriber->getCalls();
        $this->assertTrue(isset($calls[Events::PRE_TRANSITION]));
        $this->assertFalse(isset($calls[Events::POST_TRANSITION]));

        $this->assertEquals('state2', $testDoc->getState());
    }
}
