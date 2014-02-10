<?php

namespace Zoop\Shard\Test\Crypt;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Crypt\TestAsset\Document\MultipleHash;

class HashHelperTest extends BaseTest
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
                    'extension.odmcore' => true
                ],
                'service_manager_config' => [
                    'invokables' => [
                        'testsalt' => 'Zoop\Shard\Test\Crypt\TestAsset\Salt'
                    ],
                ]
           ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
        $this->hashHelper = $manifest->getServiceManager()->get('crypt.hashhelper');
    }

    public function testHashAllFields()
    {
        $testDoc = new MultipleHash();
        $testDoc->setFirstname('Tommy');
        $testDoc->setLastname('Hatchling');

        $this->hashHelper->hashDocument($testDoc, $this->documentManager->getClassMetadata(get_class($testDoc)));

        $this->assertNotEquals('Tommy', $testDoc->getFirstname());
        $this->assertNotEquals('Hatchling', $testDoc->getLastname());
    }

    public function testHashSingleField()
    {
        $testDoc = new MultipleHash();
        $testDoc->setFirstname('Tommy');
        $testDoc->setLastname('Hatchling');

        $this->hashHelper->hashField('firstname', $testDoc, $this->documentManager->getClassMetadata(get_class($testDoc)));

        $this->assertNotEquals('Tommy', $testDoc->getFirstname());
        $this->assertEquals('Hatchling', $testDoc->getLastname());
    }
}
