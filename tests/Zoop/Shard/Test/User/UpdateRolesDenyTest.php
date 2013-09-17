<?php

namespace Zoop\Shard\Test\User;

use Zoop\Shard\AccessControl\Events as Events;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\User\TestAsset\Document\User;

class UpdateRolesDenyTest extends BaseTest
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

    public function testUpdateRolesDeny()
    {
        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(Events::UPDATE_DENIED, $this);

        $testDoc = new User();
        $testDoc->setUsername('test-name');

        $documentManager->persist($testDoc);
        $documentManager->flush();

        $documentManager->clear();
        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find('test-name');

        $testDoc->addRole('user');
        $documentManager->flush();

        $this->assertTrue(isset($this->calls[Events::UPDATE_DENIED]));
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
