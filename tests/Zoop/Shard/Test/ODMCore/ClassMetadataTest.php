<?php

namespace Zoop\Shard\Test\ODMCore;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\ODMCore\TestAsset\Document\Album;
use Zoop\Shard\Test\BaseTest;

class ClassMetadataTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'models' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                'extension.serializer' => true,
                    'extension.odmcore' => true
                ],
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
    }

    public function testGetFieldValue()
    {
        //load some metadata
        $metadata = $this->documentManager->getClassMetadata('Zoop\Shard\Test\ODMCore\TestAsset\Document\Album');

        //create a document
        $document = new Album('ten');

        $this->assertEquals('ten', $metadata->getFieldValue($document, 'name'));
    }

    public function testGetFieldValueWithProxy()
    {
        //create and persist a document
        $document = new Album('ten');
        $this->documentManager->persist($document);
        $this->documentManager->flush();
        $this->documentManager->clear();

        //get the proxy
        $proxy = $this->documentManager->getReference(
            'Zoop\Shard\Test\ODMCore\TestAsset\Document\Album',
            $document->getId()
        );

        //get metadata
        $metadata = $this->documentManager->getClassMetadata('Zoop\Shard\Test\ODMCore\TestAsset\Document\Album');

        $this->assertEquals('ten', $metadata->getFieldValue($proxy, 'name'));
    }

    public function testGetIdentifierFieldValueWithProxy()
    {
        //create and persist a document
        $document = new Album('ten');
        $this->documentManager->persist($document);
        $this->documentManager->flush();
        $this->documentManager->clear();

        //get the proxy
        $proxy = $this->documentManager->getReference(
            'Zoop\Shard\Test\ODMCore\TestAsset\Document\Album',
            $document->getId()
        );

        //get metadata
        $metadata = $this->documentManager->getClassMetadata('Zoop\Shard\Test\ODMCore\TestAsset\Document\Album');

        $metadata->getFieldValue($proxy, 'id');

        $this->assertFalse($proxy->__isInitialized());
    }

    public function testSetFieldValue()
    {
        //load some metadata
        $metadata = $this->documentManager->getClassMetadata('Zoop\Shard\Test\ODMCore\TestAsset\Document\Album');

        //create a document
        $document = new Album('ten');

        //set a field
        $metadata->setFieldValue($document, 'name', 'nevermind');

        $this->assertEquals('nevermind', $document->getName());
    }

    public function testSetFieldValueWithProxy()
    {
        //create and persist a document
        $document = new Album('ten');
        $this->documentManager->persist($document);
        $this->documentManager->flush();
        $this->documentManager->clear();

        //get the proxy
        $proxy = $this->documentManager->getReference(
            'Zoop\Shard\Test\ODMCore\TestAsset\Document\Album',
            $document->getId()
        );

        //get metadata
        $metadata = $this->documentManager->getClassMetadata('Zoop\Shard\Test\ODMCore\TestAsset\Document\Album');

        //set a field
        $metadata->setFieldValue($proxy, 'name', 'nevermind');

        //flush changes
        $this->documentManager->flush();
        $this->documentManager->clear();

        //check that changes did get flushed
        $proxy = $this->documentManager->getReference(
            'Zoop\Shard\Test\ODMCore\TestAsset\Document\Album',
            $document->getId()
        );

        $this->assertEquals('nevermind', $proxy->getName());
    }
}
