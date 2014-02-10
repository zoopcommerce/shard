<?php

namespace Zoop\Shard\Test\ODMCore;

use Zoop\Shard\Manifest;
use Zoop\Shard\Core\Events;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\ODMCore\TestAsset\Document\Simple;

class ExceptionEventsAggregatorTest extends BaseTest
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
                    'extension.odmcore'   => true,
                    'extension.validator' => true
                ],
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');

        $eventManager = $this->documentManager->getEventManager();
        $eventManager->addEventListener(Events::EXCEPTION, $this);

        $this->calls = [];
    }

    public function testInvalidCreate()
    {
        $documentManager = $this->documentManager;

        $testDoc = new Simple();
        $testDoc->setName('invalid');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertTrue(isset($this->calls[Events::EXCEPTION]));
        $this->assertEquals('invalidModel', $this->calls[Events::EXCEPTION][0]->getName());
        $this->assertCount(
            2,
            $this->calls[Events::EXCEPTION][0]->getInnerEvent()->getResult()->getFieldResults()['name']->getMessages()
        );

        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $this->assertNull($testDoc);
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
