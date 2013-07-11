<?php

namespace Zoop\Shard\Test\User;

use Zoop\Shard\AccessControl\Events as Events;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\User\TestAsset\Document\User;

class UpdateRolesDenyTest extends BaseTest {

    protected $calls = array();

    public function setUp(){

        $manifest = new Manifest([
            'documents' => [
                __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
            ],
            'extension_configs' => [
                'extension.accessControl' => true
            ],
            'document_manager' => 'testing.documentmanager',
            'service_manager_config' => [
                'factories' => [
                    'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                    'user' => function(){
                        $user = new User();
                        $user->setUsername('toby');
                        return $user;
                    }
                ]
            ]
        ]);

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');
    }

    public function testUpdateRolesDeny(){

        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::updateDenied, $this);

        $testDoc = new User();
        $testDoc->setUsername('test-name');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $documentManager->clear();
        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find('test-name');

        $testDoc->addRole('user');
        $documentManager->flush();

        $this->assertTrue(isset($this->calls[Events::updateDenied]));
    }

    public function __call($name, $arguments){
        $this->calls[$name] = $arguments;
    }
}