<?php

namespace Zoop\Shard\Test\Validator;

use Zoop\Shard\Manifest;
use Zoop\Shard\Validator\Events;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Validator\TestAsset\Document\Simple;

class ValidatorTest extends BaseTest
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
    }

    public function testRequired()
    {
        $documentManager = $this->documentManager;

        $testDoc = new Simple();

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertTrue(isset($this->calls[Events::INVALID_MODEL]));
        $this->assertCount(
            1,
            $this->calls[Events::INVALID_MODEL][0]->getResult()->getFieldResults()['name']->getMessages()
        );

        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $this->assertNull($testDoc);
    }

    public function testInvalidCreate()
    {
        $documentManager = $this->documentManager;

        $testDoc = new Simple();
        $testDoc->setName('invalid');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertTrue(isset($this->calls[Events::INVALID_MODEL]));
        $this->assertCount(
            2,
            $this->calls[Events::INVALID_MODEL][0]->getResult()->getFieldResults()['name']->getMessages()
        );

        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $this->assertNull($testDoc);
    }

    public function testValidCreate()
    {
        $documentManager = $this->documentManager;

        $testDoc = new Simple();
        $testDoc->setName('valid');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertFalse(isset($this->calls[Events::INVALID_MODEL]));

        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $this->assertEquals('valid', $testDoc->getName());
    }

    public function testInvalidUpdate()
    {
        $documentManager = $this->documentManager;

        $testDoc = new Simple();
        $testDoc->setName('valid');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $testDoc->setName('invalid');
        $documentManager->flush();

        $this->assertTrue(isset($this->calls[Events::INVALID_MODEL]));
        $this->assertCount(
            2,
            $this->calls[Events::INVALID_MODEL][0]->getResult()->getFieldResults()['name']->getMessages()
        );

        $documentManager->clear();
        $testDoc = $repository->find($id);

        $this->assertEquals('valid', $testDoc->getName());
    }

    public function testValidUpdate()
    {
        $documentManager = $this->documentManager;

        $testDoc = new Simple();
        $testDoc->setName('valid');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $testDoc->setName('alsoValid');
        $documentManager->flush();

        $this->assertFalse(isset($this->calls[Events::INVALID_MODEL]));

        $documentManager->clear();
        $testDoc = $repository->find($id);

        $this->assertEquals('alsoValid', $testDoc->getName());
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
