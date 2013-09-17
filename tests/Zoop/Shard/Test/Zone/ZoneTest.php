<?php

namespace Zoop\Shard\Test\Zone;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Zone\TestAsset\Document\Simple;

class ZoneTest extends BaseTest
{
    public function setUp()
    {

        $manifest = new Manifest(
            [
                'object_map' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.zone' => true,
                    'extension.odmcore' => true
                ],
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('objectmanager');
        $this->extension = $manifest->getServiceManager()->get('extension.zone');
    }

    public function testBasicFunction()
    {

        $documentManager = $this->documentManager;
        $testDoc = new Simple();

        $testDoc->setName('miriam');
        $testDoc->setZones(['zone1', 'zone2']);
        $testDoc->addZone('zone3');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = null;
        $testDoc = $repository->find($id);

        $zones = $testDoc->getZones();
        sort($zones);
        $this->assertEquals(['zone1', 'zone2', 'zone3'], $zones);

        $testDoc->removeZone('zone3');

        $zones = $testDoc->getZones();
        sort($zones);
        $this->assertEquals(['zone1', 'zone2'], $zones);
    }

    public function testFilter()
    {

        $documentManager = $this->documentManager;

        $testDocA = new Simple();
        $testDocA->setName('miriam');
        $testDocA->setZones(['zone1', 'zone2']);

        $testDocB = new Simple();
        $testDocB->setName('lucy');
        $testDocB->setZones(['zone2', 'zone3']);

        $documentManager->persist($testDocA);
        $documentManager->persist($testDocB);
        $documentManager->flush();
        $ids = [$testDocA->getId(), $testDocB->getId()];
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(['lucy', 'miriam'], $docNames);

        $this->extension->setReadFilterInclude(['zone1']);

        $documentManager->flush();
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(['miriam'], $docNames);

        $this->extension->setReadFilterInclude(['zone1', 'zone3']);
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(['lucy', 'miriam'], $docNames);

        $this->extension->setReadFilterInclude(['zone2']);
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(['lucy', 'miriam'], $docNames);

        $this->extension->setReadFilterInclude();
        $this->extension->setReadFilterExclude(['zone2']);
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertCount(0, $docNames);

        $this->extension->setReadFilterExclude(['zone1']);
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(['lucy'], $docNames);

        $this->extension->setReadFilterInclude();
        $this->extension->setReadFilterExclude();
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(['lucy', 'miriam'], $docNames);
    }

    protected function getTestDocs()
    {
        $repository = $this->documentManager->getRepository('Zoop\Shard\Test\Zone\TestAsset\Document\Simple');
        $testDocs = $repository->findAll();
        $returnDocs = [];
        $returnNames = [];
        foreach ($testDocs as $testDoc) {
            $returnDocs[] = $testDoc;
            $returnNames[] = $testDoc->getName();
        }
        sort($returnNames);

        return [$returnDocs, $returnNames];
    }
}
