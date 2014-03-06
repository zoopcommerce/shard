<?php

namespace Zoop\Shard\Test\Crypt;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Crypt\TestAsset\Document\MultipleBlockCipher;

class BlockCipherHelperTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'models' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.crypt' => true,
                    'extension.odmcore' => $this->getOdmCoreConfig()
                ],
                'service_manager_config' => [
                    'invokables' => [
                        'testkey' => 'Zoop\Shard\Test\Crypt\TestAsset\Key'
                    ],
                ]
           ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
        $this->blockCipherHelper = $manifest->getServiceManager()->get('crypt.blockcipherhelper');
    }

    public function testEncryptDecryptAllFields()
    {
        $testDoc = new MultipleBlockCipher();
        $testDoc->setFirstname('Tommy');
        $testDoc->setLastname('Hatchling');

        $this->blockCipherHelper->encryptDocument(
            $testDoc,
            $this->documentManager->getClassMetadata(get_class($testDoc))
        );

        $this->assertNotEquals('Tommy', $testDoc->getFirstname());
        $this->assertNotEquals('Hatchling', $testDoc->getLastname());

        $this->blockCipherHelper->decryptDocument(
            $testDoc,
            $this->documentManager->getClassMetadata(get_class($testDoc))
        );

        $this->assertEquals('Tommy', $testDoc->getFirstname());
        $this->assertEquals('Hatchling', $testDoc->getLastname());
    }

    public function testEncryptDecryptSingleField()
    {
        $testDoc = new MultipleBlockCipher();
        $testDoc->setFirstname('Tommy');
        $testDoc->setLastname('Hatchling');

        $this->blockCipherHelper->encryptField(
            'firstname',
            $testDoc,
            $this->documentManager->getClassMetadata(get_class($testDoc))
        );

        $this->assertNotEquals('Tommy', $testDoc->getFirstname());
        $this->assertEquals('Hatchling', $testDoc->getLastname());

        $this->blockCipherHelper->decryptField(
            'firstname',
            $testDoc,
            $this->documentManager->getClassMetadata(get_class($testDoc))
        );

        $this->assertEquals('Tommy', $testDoc->getFirstname());
        $this->assertEquals('Hatchling', $testDoc->getLastname());
    }
}
