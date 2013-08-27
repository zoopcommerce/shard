<?php

namespace Zoop\Shard\Test\SoftDelete;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\SoftDelete\TestAsset\Document\Stamped;
use Zoop\Shard\Test\TestAsset\User;

class StampTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'documents' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.softdelete' => true
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
        $this->softDeleter = $manifest->getServiceManager()->get('softDeleter');
    }

    public function testStamps()
    {
        $documentManager = $this->documentManager;
        $testDoc = new Stamped();
        $testDoc->setName('version1');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertNull($testDoc->getSoftDeletedBy());
        $this->assertNull($testDoc->getSoftDeletedOn());
        $this->assertNull($testDoc->getRestoredBy());
        $this->assertNull($testDoc->getRestoredOn());

        $this->softDeleter->softDelete($testDoc);

        $documentManager->flush();
        $documentManager->clear();

        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertEquals('toby', $testDoc->getSoftDeletedBy());
        $this->assertNotNull($testDoc->getSoftDeletedOn());
        $this->assertNull($testDoc->getRestoredBy());
        $this->assertNull($testDoc->getRestoredOn());

        $this->softDeleter->restore($testDoc);

        $documentManager->flush();
        $documentManager->clear();

        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertEquals('toby', $testDoc->getSoftDeletedBy());
        $this->assertNotNull($testDoc->getSoftDeletedOn());
        $this->assertEquals('toby', $testDoc->getRestoredBy());
        $this->assertNotNull($testDoc->getRestoredOn());
    }
}
