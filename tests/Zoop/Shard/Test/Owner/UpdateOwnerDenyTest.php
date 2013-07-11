<?php

namespace Zoop\Shard\Test\Owner;

use Zoop\Shard\Manifest;
use Zoop\Shard\AccessControl\Events as Events;
use Zoop\Shard\Test\Owner\TestAsset\Document\OwnerDoc;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\TestAsset\RoleAwareUser;

class UpdateOwnerDenyTest extends BaseTest {

    protected $calls = array();

    public function setUp(){
        $manifest = new Manifest([
            'documents' => [
                __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
            ],
            'extension_configs' => [
                'extension.accessControl' => true,
                'extension.owner' => true
            ],
            'document_manager' => 'testing.documentmanager',
            'service_manager_config' => [
                'factories' => [
                    'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                    'user' => function(){
                        $user = new RoleAwareUser();
                        $user->setUsername('toby');
                        return $user;
                    }
                ]
            ]
        ]);

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');
    }

    public function testUpdateOwnerDeny(){

        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::updateDenied, $this);

        $testDoc = new OwnerDoc();

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();

        $documentManager->clear();
        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $testDoc->setOwner('bobby');
        $documentManager->flush();

        $this->assertTrue(isset($this->calls[Events::updateDenied]));
    }

    public function __call($name, $arguments){
        $this->calls[$name] = $arguments;
    }
}