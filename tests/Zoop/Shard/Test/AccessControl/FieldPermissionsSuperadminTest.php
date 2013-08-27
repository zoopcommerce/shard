<?php

namespace Zoop\Shard\Test\AccessControl;

use Zoop\Shard\AccessControl\Events as AccessControlEvents;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\AccessControl\TestAsset\Document\FieldPermissions;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\TestAsset\RoleAwareUser;

class FieldPermissionsSuperadminTest extends BaseTest
{
    protected $calls = array();

    public function setUp()
    {
        $manifest = new Manifest(
            [
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
                        'user' => function () {
                            $user = new RoleAwareUser();
                            $user->setUsername('toby');
                            $user->addRole('superadmin');
                            return $user;
                        }
                    ]
                ]
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');
    }

    public function testUpdateAllow()
    {
        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(AccessControlEvents::UPDATE_DENIED, $this);

        $testDoc = new FieldPermissions();
        $testDoc->setName('my name');
        $testDoc->setAddress('my address');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();

        $documentManager->clear();
        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $testDoc->setName('new name');
        $testDoc->setAddress('new address');

        $documentManager->flush();

        $this->assertFalse(isset($this->calls[AccessControlEvents::UPDATE_DENIED]));

        $documentManager->clear();
        $testDoc = $repository->find($id);

        $this->assertEquals('new name', $testDoc->getName());
        $this->assertEquals('new address', $testDoc->getAddress());
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
