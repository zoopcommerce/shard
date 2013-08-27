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
                'documents' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.validator' => true
                ],
                'document_manager' => 'testing.documentmanager',
                'service_manager_config' => [
                    'factories' => [
                        'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                    ]
                ]
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');

        $eventManager = $this->documentManager->getEventManager();
        $eventManager->addEventListener(Events::INVALID_CREATE, $this);
        $eventManager->addEventListener(Events::INVALID_UPDATE, $this);

        $this->calls = array();
    }

    public function testRequired()
    {
        $documentManager = $this->documentManager;

        $testDoc = new Simple();

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertTrue(isset($this->calls[Events::INVALID_CREATE]));
        $this->assertFalse(isset($this->calls[Events::INVALID_UPDATE]));
        $this->assertCount(
            1,
            $this->calls[Events::INVALID_CREATE][0]->getResult()->getFieldResults()['name']->getMessages()
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

        $this->assertTrue(isset($this->calls[Events::INVALID_CREATE]));
        $this->assertFalse(isset($this->calls[Events::INVALID_UPDATE]));
        $this->assertCount(
            2,
            $this->calls[Events::INVALID_CREATE][0]->getResult()->getFieldResults()['name']->getMessages()
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

        $this->assertFalse(isset($this->calls[Events::INVALID_CREATE]));
        $this->assertFalse(isset($this->calls[Events::INVALID_UPDATE]));

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

        $this->assertFalse(isset($this->calls[Events::INVALID_CREATE]));
        $this->assertTrue(isset($this->calls[Events::INVALID_UPDATE]));
        $this->assertCount(
            2,
            $this->calls[Events::INVALID_UPDATE][0]->getResult()->getFieldResults()['name']->getMessages()
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

        $this->assertFalse(isset($this->calls[Events::INVALID_CREATE]));
        $this->assertFalse(isset($this->calls[Events::INVALID_UPDATE]));

        $documentManager->clear();
        $testDoc = $repository->find($id);

        $this->assertEquals('alsoValid', $testDoc->getName());
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
