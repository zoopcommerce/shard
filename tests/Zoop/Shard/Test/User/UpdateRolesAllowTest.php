<?php

namespace Zoop\Shard\Test\User;

use Zoop\Shard\AccessControl\Events as Events;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\User\TestAsset\Document\User;

class UpdateRolesAllowTest extends BaseTest
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
                    'extension.odmcore' => $this->getOdmCoreConfig()
                ],
                'service_manager_config' => [
                    'factories' => [
                        'user' => function () {
                            $user = new User();
                            $user->setUsername('toby');
                            $user->addRole('admin');

                            return $user;
                        }
                    ]
                ]
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
    }

    public function testRolesUpdateAllow()
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

        $testDoc->addRole('editor');
        $documentManager->flush();

        $this->assertFalse(isset($this->calls[Events::UPDATE_DENIED]));
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
