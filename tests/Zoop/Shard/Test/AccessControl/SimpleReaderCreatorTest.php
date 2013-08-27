<?php

namespace Zoop\Shard\Test\AccessControl;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\AccessControl\TestAsset\Document\Simple;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\TestAsset\RoleAwareUser;

class SimpleReaderCreatorTest extends BaseTest
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
                            $user->addRole('creator');
                            $user->addRole('reader');
                            return $user;
                        }
                    ]
                ]
           ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');
    }

    public function testReadControlAllow()
    {
        $documentManager = $this->documentManager;

        $toby = new Simple();
        $toby->setName('toby');
        $miriam = new Simple();
        $miriam->setName('miriam');
        $documentManager->persist($toby);
        $documentManager->persist($miriam);
        $documentManager->flush();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($toby));

        $toby = $repository->find($toby->getId());
        $this->assertNotNull($toby);
        $miriam = $repository->find($miriam->getId());
        $this->assertNotNull($miriam);
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
