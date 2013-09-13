<?php

namespace Zoop\Shard\Test\User;

use Zoop\Shard\Crypt\Hash\BasicHashService;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\User\TestAsset\Document\PasswordTraitDoc;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\TestAsset\User;

class PasswordTraitTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'documents' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.crypt' => true,
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

    public function testPassword()
    {
        $hash = new BasicHashService();

        $password = 'password1';
        $doc = new PasswordTraitDoc();

        $doc->setPassword($password);

        $this->documentManager->persist($doc);
        $this->documentManager->flush();

        $this->assertNotEquals($password, $doc->getPassword());
        $this->assertEquals($doc->getPassword(), $hash->hashValue($password, $doc->getSalt()));
        $this->assertNotEquals($doc->getPassword(), $hash->hashValue('not password', $doc->getSalt()));

        $newPassword = 'new password';
        $doc->setPassword($newPassword);

        $this->documentManager->flush();

        $this->assertNotEquals($newPassword, $doc->getPassword());
        $this->assertEquals($doc->getPassword(), $hash->hashValue($newPassword, $doc->getSalt()));
        $this->assertNotEquals($doc->getPassword(), $hash->hashValue($password, $doc->getSalt()));
    }
}
