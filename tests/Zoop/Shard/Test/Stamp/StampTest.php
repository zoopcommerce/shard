<?php

namespace Zoop\Shard\Test\Stamp;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Stamp\TestAsset\Document\Simple;
use Zoop\Shard\Test\TestAsset\User;

class StampTest extends BaseTest
{
    protected $subscriber;

    public function setUp()
    {
        $manifest = new Manifest(
            [
                'documents' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.stamp' => true
                ],
                'document_manager' => 'testing.documentmanager',
                'service_manager_config' => [
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
    }

    public function testStamp()
    {
        $documentManager = $this->documentManager;
        $testDoc = new Simple();
        $testDoc->setName('version1');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertEquals('version1', $testDoc->getName());
        $this->assertEquals('toby', $testDoc->getCreatedBy());
        $this->assertNotNull($testDoc->getCreatedOn());
        $this->assertEquals('toby', $testDoc->getUpdatedBy());
        $this->assertNotNull($testDoc->getUpdatedOn());

        $testDoc->setName('version2');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();
        $documentManager->clear();

        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertEquals('version2', $testDoc->getName());
        $this->assertEquals('toby', $testDoc->getCreatedBy());
        $this->assertNotNull($testDoc->getCreatedOn());
        $this->assertEquals('toby', $testDoc->getUpdatedBy());
        $this->assertNotNull($testDoc->getUpdatedOn());
    }
}
