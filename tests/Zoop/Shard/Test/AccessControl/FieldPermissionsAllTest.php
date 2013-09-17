<?php

namespace Zoop\Shard\Test\AccessControl;

use Zoop\Shard\AccessControl\Events as AccessControlEvents;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\AccessControl\TestAsset\Document\FieldPermissions;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\TestAsset\User;

class FieldPermissionsAllTest extends BaseTest
{
    protected $calls = array();

    public function setUp()
    {
        $manifest = new Manifest(
            [
                'object_map' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.accessControl' => true,
                    'extension.odmcore' => true
                ],
                'service_manager_config' => [
                    'factories' => [
                        'user' => function () {
                            $user = new User();
                            $user->setUsername('toby');

                            return $user;
                        }
                    ]
                ]
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('objectmanager');
    }

    public function testUpdateDeny()
    {
        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(AccessControlEvents::UPDATE_DENIED, $this);

        $testDoc = new FieldPermissions();
        $testDoc->setName('my name');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();

        $documentManager->clear();
        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $testDoc->setName('new name');
        $documentManager->flush();

        $this->assertTrue(isset($this->calls[AccessControlEvents::UPDATE_DENIED]));

        $documentManager->clear();
        $testDoc = $repository->find($id);

        $this->assertEquals('my name', $testDoc->getName());
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
