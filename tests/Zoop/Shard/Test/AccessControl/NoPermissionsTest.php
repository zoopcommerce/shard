<?php

namespace Zoop\Shard\Test\AccessControl;

use Zoop\Shard\AccessControl\Events as AccessControlEvents;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\AccessControl\TestAsset\Document\NoPermissions;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\TestAsset\RoleAwareUser;

class NoPermissionsTest extends BaseTest
{
    protected $calls = array();

    public function setUp()
    {
        $manifest = new Manifest(
            [
                'models' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.accessControl' => true,
                    'extension.odmcore' => true
                ],
                'service_manager_config' => [
                    'factories' => [
                        'user' => function () {
                            $user = new RoleAwareUser();
                            $user->setUsername('toby')->addRole('admin');

                            return $user;
                        }
                    ]
                ]
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
    }

    public function testNoPermissions()
    {
        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(AccessControlEvents::CREATE_DENIED, $this);

        $testDoc = new NoPermissions();
        $testDoc->setName('nathan');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $documentManager->flush();

        $this->assertTrue(isset($this->calls[AccessControlEvents::CREATE_DENIED]));
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
