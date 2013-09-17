<?php

namespace Zoop\Shard\Test\Crypt;

use Zoop\Shard\Validator\Events;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Crypt\TestAsset\Document\CryptValidatorDoc;

class CryptValidatorTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'model_map' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.crypt' => true,
                    'extension.validator' => true,
                    'extension.odmcore' => true
                ],
                'service_manager_config' => [
                    'invokables' => [
                        'testkey' => 'Zoop\Shard\Test\Crypt\TestAsset\Key'
                    ],
                ]
           ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
        $this->blockCipherHelper = $manifest->getServiceManager()->get('crypt.blockcipherhelper');

        $eventManager = $this->documentManager->getEventManager();
        $eventManager->addEventListener(Events::INVALID_OBJECT, $this);

        $this->calls = [];
    }

    public function testCryptValidator()
    {
        $documentManager = $this->documentManager;

        $testDoc = new CryptValidatorDoc();
        $testDoc->setName('Toby');
        $testDoc->setEmail('invalid email');

        $metadata = $documentManager->getClassMetadata(get_class($testDoc));

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $documentManager->clear();

        $this->assertCount(1, $this->calls);
        $this->calls = [];

        $testDoc->setEmail('toby@here.com');
        $documentManager->persist($testDoc);
        $documentManager->flush();

        $this->assertCount(0, $this->calls);
        $this->calls = [];

        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $this->assertNotEquals('toby@here.com', $testDoc->getEmail());
        $this->blockCipherHelper->decryptDocument($testDoc, $metadata);
        $this->assertEquals('toby@here.com', $testDoc->getEmail());

        $testDoc->setName('Lucy');
        $documentManager->flush();
        $documentManager->clear();

        $this->assertCount(0, $this->calls);
        $this->calls = [];

        $testDoc = $repository->find($id);

        $testDoc->setEmail('lucy@here.com');
        $documentManager->flush();
        $documentManager->clear();

        $this->assertCount(0, $this->calls);
        $this->calls = [];

        $testDoc = $repository->find($id);

        $this->assertNotEquals('lucy@here.com', $testDoc->getEmail());
        $this->blockCipherHelper->decryptDocument($testDoc, $metadata);
        $this->assertEquals('lucy@here.com', $testDoc->getEmail());

        $testDoc->setEmail('invalid email');

        $documentManager->flush();
        $documentManager->clear();

        $this->assertCount(1, $this->calls);

        $testDoc = $repository->find($id);
        $this->blockCipherHelper->decryptDocument($testDoc, $metadata);
        $this->assertEquals('lucy@here.com', $testDoc->getEmail());
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
