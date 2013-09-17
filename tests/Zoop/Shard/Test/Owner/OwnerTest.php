<?php

namespace Zoop\Shard\Test\Owner;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\Owner\TestAsset\Document\OwnerDoc;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\TestAsset\User;

class OwnerTraitTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'model_map' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.owner' => true,
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

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
    }

    public function testOwner()
    {
        $doc = new OwnerDoc();
        $this->documentManager->persist($doc);
        $this->documentManager->flush();

        $this->assertEquals('toby', $doc->getOwner());

        $doc->setOwner('bobby');

        $this->documentManager->flush();

        $this->assertEquals('bobby', $doc->getOwner());
    }
}
