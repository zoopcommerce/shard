<?php

namespace Zoop\Shard\Test\Crypt;

use Zoop\Shard\Crypt\BlockCipherHelper;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Crypt\TestAsset\Document\Simple;
use Zoop\Shard\Test\TestAsset\User;

class BlockCipherTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'documents' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.crypt' => true
                ],
                'document_manager' => 'testing.documentmanager',
                'service_manager_config' => [
                    'invokables' => [
                        'testkey' => 'Zoop\Shard\Test\Crypt\TestAsset\Key'
                    ],
                    'factories' => [
                        'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                        'user' => function () {
                            $user = new User();
                            $user->setUsername('toby');
                            return $user;
                        }
                    ]
                ]
           ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');
        $this->blockCipherHelper = $manifest->getServiceManager()->get('crypt.blockcipherhelper');
    }

    public function testBlockCipher()
    {
        $documentManager = $this->documentManager;

        $testDoc = new Simple();
        $testDoc->setName('Toby');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $this->assertNotEquals('Toby', $testDoc->getName());

        $this->blockCipherHelper->decryptDocument($testDoc, $documentManager->getClassMetadata(get_class($testDoc)));

        $this->assertEquals('Toby', $testDoc->getName());

        $testDoc->setName('Lucy');

        $documentManager->flush();
        $documentManager->clear();

        $testDoc = $repository->find($id);

        $this->assertNotEquals('Lucy', $testDoc->getName());

        $this->blockCipherHelper->decryptDocument($testDoc, $documentManager->getClassMetadata(get_class($testDoc)));

        $this->assertEquals('Lucy', $testDoc->getName());
    }
}
