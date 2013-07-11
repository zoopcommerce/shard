<?php

namespace Zoop\Shard\Test\Zone;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Zone\TestAsset\Document\Simple;

class ZoneTest extends BaseTest {

    public function setUp(){

        $manifest = new Manifest([
            'documents' => [
                __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
            ],
            'extension_configs' => [
                'extension.zone' => true
            ],
            'document_manager' => 'testing.documentmanager',
            'service_manager_config' => [
                'factories' => [
                    'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                ]
            ]
        ]);

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');
    }

    public function testBasicFunction(){

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

    public function testFilter() {

        $documentManager = $this->documentManager;
        $documentManager->getFilterCollection()->enable('zone');

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

        $documentManager->getFilterCollection()->enable('zone');
        $filter = $documentManager->getFilterCollection()->getFilter('zone');

        $filter->setZones(['zone1']);

        $documentManager->flush();
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(['miriam'], $docNames);

        $filter->setZones(['zone1', 'zone3']);
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(['lucy', 'miriam'], $docNames);

        $filter->setZones(['zone2']);
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(['lucy', 'miriam'], $docNames);

        $filter->excludeZoneList();
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertCount(0, $docNames);

        $filter->setZones(['zone1']);
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(['lucy'], $docNames);

        $documentManager->getFilterCollection()->disable('zone');
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(['lucy', 'miriam'], $docNames);
    }

    protected function getTestDocs(){
        $repository = $this->documentManager->getRepository('Zoop\Shard\Test\Zone\TestAsset\Document\Simple');
        $testDocs = $repository->findAll();
        $returnDocs = [];
        $returnNames = [];
        foreach ($testDocs as $testDoc){
            $returnDocs[] = $testDoc;
            $returnNames[] = $testDoc->getName();
        }
        sort($returnNames);
        return [$returnDocs, $returnNames];
    }
}