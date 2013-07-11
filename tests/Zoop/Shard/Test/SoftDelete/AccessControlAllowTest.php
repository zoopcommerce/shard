<?php

namespace Zoop\Shard\Test\SoftDelete;

use Zoop\Shard\Manifest;
use Zoop\Shard\SoftDelete\Events;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\SoftDelete\TestAsset\Document\AccessControlled;
use Zoop\Shard\Test\TestAsset\RoleAwareUser;

class AccessControlAllowTest extends BaseTest {

    protected $calls = array();

    public function setUp(){

        $manifest = new Manifest([
            'documents' => [
                __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
            ],
            'extension_configs' => [
                'extension.softDelete' => true,
                'extension.accessControl' => true
            ],
            'document_manager' => 'testing.documentmanager',
            'service_manager_config' => [
                'factories' => [
                    'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                    'user' => function(){
                        $user = new RoleAwareUser();
                        $user->setUsername('toby');
                        $user->addRole('admin');
                        return $user;
                    }
                ]
            ]
        ]);

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');
        $this->softDeleter = $manifest->getServiceManager()->get('softDeleter');
    }

    public function testSoftDeleteAllow(){

        $this->calls = array();

        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::softDeleteDenied, $this);

        $testDoc = new AccessControlled();

        $testDoc->setName('version 1');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $this->softDeleter->softDelete($testDoc);

        $documentManager->flush();
        $documentManager->clear();

        $testDoc = $repository->find($id);
        $this->assertTrue($this->softDeleter->isSoftDeleted($testDoc));
        $this->assertFalse(isset($this->calls[Events::softDeleteDenied]));
    }

    public function testRestoreGrant(){

        $this->calls = array();

        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::restoreDenied, $this);

        $testDoc = new AccessControlled();
        $this->softDeleter->softDelete($testDoc);
        $testDoc->setName('version 1');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $this->softDeleter->restore($testDoc);

        $documentManager->flush();
        $documentManager->clear();

        $testDoc = $repository->find($id);
        $this->assertFalse($this->softDeleter->isSoftDeleted($testDoc));
        $this->assertFalse(isset($this->calls[Events::restoreDenied]));
    }

    public function __call($name, $arguments){
        $this->calls[$name] = $arguments;
    }
}