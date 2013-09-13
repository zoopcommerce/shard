<?php

namespace Zoop\Shard\Test\SoftDelete;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\Manifest;
use Zoop\Shard\SoftDelete\Events;
use Zoop\Shard\SoftDelete\SoftDeleteEventArgs;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\TestAsset\User;
use Zoop\Shard\Test\SoftDelete\TestAsset\Document\Simple;

class SoftDeleteTest extends BaseTest implements EventSubscriber
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'documents' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.softdelete' => true,
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
        $this->softDeleter = $manifest->getServiceManager()->get('softDeleter');
    }

    public function testBasicFunction()
    {
        $documentManager = $this->documentManager;
        $testDoc = new Simple();
        $metadata = $documentManager->getClassMetadata(get_class($testDoc));

        $testDoc->setName('version 1');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();
        $documentManager->clear();

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertFalse($this->softDeleter->isSoftDeleted($testDoc, $metadata));

        $this->softDeleter->softDelete($testDoc, $metadata);

        $documentManager->flush();
        $documentManager->clear();
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertTrue($this->softDeleter->isSoftDeleted($testDoc, $metadata));

        $testDoc->setName('version 2');

        $documentManager->flush();
        $documentManager->clear();
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertEquals('version 1', $testDoc->getName());

        $this->softDeleter->restore($testDoc, $metadata);

        $documentManager->flush();
        $documentManager->clear();
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertFalse($this->softDeleter->isSoftDeleted($testDoc, $metadata));
    }

    public function testFilter()
    {
        $documentManager = $this->documentManager;
        $documentManager->getFilterCollection()->enable('softDelete');

        $testDocA = new Simple();
        $testDocA->setName('miriam');

        $testDocB = new Simple();
        $testDocB->setName('lucy');

        $metadata = $documentManager->getClassMetadata(get_class($testDocA));

        $documentManager->persist($testDocA);
        $documentManager->persist($testDocB);
        $documentManager->flush();
        $ids = array($testDocA->getId(), $testDocB->getId());
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('lucy', 'miriam'), $docNames);

        if ($testDocs[0]->getName() == 'lucy') {
            $this->softDeleter->softDelete($testDocs[0], $metadata);
        } else {
            $this->softDeleter->softDelete($testDocs[1], $metadata);
        }

        $documentManager->flush();
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('miriam'), $docNames);

        $filter = $documentManager->getFilterCollection()->getFilter('softDelete');
        $filter->onlySoftDeleted();
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('lucy'), $docNames);

        $filter->onlyNotSoftDeleted();
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('miriam'), $docNames);

        $documentManager->getFilterCollection()->disable('softDelete');

        $documentManager->flush();
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('lucy', 'miriam'), $docNames);

        if ($testDocs[0]->getName() == 'lucy') {
            $this->softDeleter->restore($testDocs[0], $metadata);
        } else {
            $this->softDeleter->restore($testDocs[1], $metadata);
        }

        $documentManager->getFilterCollection()->enable('softDelete');

        $documentManager->flush();
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('lucy', 'miriam'), $docNames);
    }

    protected function getTestDocs()
    {
        $repository = $this->documentManager->getRepository('Zoop\Shard\Test\SoftDelete\TestAsset\Document\Simple');
        $testDocs = $repository->findAll();
        $returnDocs = array();
        $returnNames = array();
        foreach ($testDocs as $testDoc) {
            $returnDocs[] = $testDoc;
            $returnNames[] = $testDoc->getName();
        }
        sort($returnNames);

        return array($returnDocs, $returnNames);
    }

    public function testEvents()
    {
        $subscriber = $this;

        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();
        $eventManager->addEventSubscriber($subscriber);

        $testDoc = new Simple();
        $metadata = $documentManager->getClassMetadata(get_class($testDoc));

        $testDoc->setName('version 1');

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $id = $testDoc->getId();
        $documentManager->clear();

        $calls = $subscriber->getCalls();
        $this->assertFalse(isset($calls[Events::PRE_SOFT_DELETE]));
        $this->assertFalse(isset($calls[Events::POST_SOFT_DELETE]));
        $this->assertFalse(isset($calls[Events::PRE_RESTORE]));
        $this->assertFalse(isset($calls[Events::POST_RESTORE]));

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertFalse($this->softDeleter->isSoftDeleted($testDoc, $metadata));

        $subscriber->reset();
        $this->softDeleter->softDelete($testDoc, $metadata);

        $documentManager->flush();

        $calls = $subscriber->getCalls();
        $this->assertTrue(isset($calls[Events::PRE_SOFT_DELETE]));
        $this->assertTrue(isset($calls[Events::POST_SOFT_DELETE]));
        $this->assertFalse(isset($calls[Events::PRE_RESTORE]));
        $this->assertFalse(isset($calls[Events::POST_RESTORE]));

        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertTrue($this->softDeleter->isSoftDeleted($testDoc, $metadata));

        $testDoc->setName('version 2');
        $subscriber->reset();
        $documentManager->flush();

        $calls = $subscriber->getCalls();
        $this->assertTrue(isset($calls[Events::SOFT_DELETED_UPDATE_DENIED]));

        $subscriber->reset();
        $this->softDeleter->restore($testDoc, $metadata);

        $documentManager->flush();

        $calls = $subscriber->getCalls();
        $this->assertFalse(isset($calls[Events::PRE_SOFT_DELETE]));
        $this->assertFalse(isset($calls[Events::POST_SOFT_DELETE]));
        $this->assertTrue(isset($calls[Events::PRE_RESTORE]));
        $this->assertTrue(isset($calls[Events::POST_RESTORE]));

        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertFalse($this->softDeleter->isSoftDeleted($testDoc, $metadata));

        $subscriber->reset();
        $subscriber->setRollbackDelete(true);
        $this->softDeleter->softDelete($testDoc, $metadata);

        $documentManager->flush();

        $calls = $subscriber->getCalls();
        $this->assertTrue(isset($calls[Events::PRE_SOFT_DELETE]));
        $this->assertFalse(isset($calls[Events::POST_SOFT_DELETE]));
        $this->assertFalse(isset($calls[Events::PRE_RESTORE]));
        $this->assertFalse(isset($calls[Events::POST_RESTORE]));

        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertFalse($this->softDeleter->isSoftDeleted($testDoc, $metadata));

        $subscriber->reset();
        $this->softDeleter->softDelete($testDoc, $metadata);
        $documentManager->flush();

        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertTrue($this->softDeleter->isSoftDeleted($testDoc, $metadata));

        $subscriber->reset();
        $subscriber->setRollbackRestore(true);
        $this->softDeleter->restore($testDoc, $metadata);

        $documentManager->flush();

        $calls = $subscriber->getCalls();
        $this->assertFalse(isset($calls[Events::PRE_SOFT_DELETE]));
        $this->assertFalse(isset($calls[Events::POST_SOFT_DELETE]));
        $this->assertTrue(isset($calls[Events::PRE_RESTORE]));
        $this->assertFalse(isset($calls[Events::POST_RESTORE]));
    }

    protected $calls = array();

    protected $rollbackDelete = false;
    protected $rollbackRestore = false;

    public function getSubscribedEvents()
    {
        return array(
            Events::PRE_SOFT_DELETE,
            Events::POST_SOFT_DELETE,
            Events::PRE_RESTORE,
            Events::POST_RESTORE,
            Events::SOFT_DELETED_UPDATE_DENIED
        );
    }

    public function reset()
    {
        $this->calls = array();
        $this->rollbackDelete = false;
        $this->rollbackRestore = false;
    }

    public function preSoftDelete(SoftDeleteEventArgs $eventArgs)
    {
        $this->calls[Events::PRE_SOFT_DELETE] = $eventArgs;
        if ($this->rollbackDelete) {
            $eventArgs->setReject(true);
        }
    }

    public function preRestore(SoftDeleteEventArgs $eventArgs)
    {
        $this->calls[Events::PRE_RESTORE] = $eventArgs;
        if ($this->rollbackRestore) {
            $eventArgs->setReject(true);
        }
    }

    public function getRollbackDelete()
    {
        return $this->rollbackDelete;
    }

    public function setRollbackDelete($rollbackDelete)
    {
        $this->rollbackDelete = $rollbackDelete;
    }

    public function getRollbackRestore()
    {
        return $this->rollbackRestore;
    }

    public function setRollbackRestore($rollbackRestore)
    {
        $this->rollbackRestore = $rollbackRestore;
    }

    public function getCalls()
    {
        return $this->calls;
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments[0];
    }
}
