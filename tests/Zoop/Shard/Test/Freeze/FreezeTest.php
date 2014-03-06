<?php

namespace Zoop\Shard\Test\Freeze;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\Freeze\Extension;
use Zoop\Shard\Freeze\Events;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Freeze\TestAsset\Document\Simple;
use Zoop\Shard\Test\TestAsset\User;
use Zoop\Shard\Freeze\FreezerEventArgs;

class FreezeTest extends BaseTest implements EventSubscriber
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'models' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.freeze' => true,
                    'extension.odmcore' => $this->getOdmCoreConfig()
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
        $this->freezer = $manifest->getServiceManager()->get('freezer');
        $this->extension = $manifest->getServiceManager()->get('extension.freeze');
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

        $this->assertFalse($this->freezer->isFrozen($testDoc, $metadata));

        $this->freezer->freeze($testDoc, $metadata);

        $documentManager->flush();
        $documentManager->clear();
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertTrue($this->freezer->isFrozen($testDoc, $metadata));

        $testDoc->setName('version 2');

        $documentManager->flush();
        $documentManager->clear();
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertEquals('version 1', $testDoc->getName());

        $documentManager->remove($testDoc);
        $documentManager->flush();
        $documentManager->clear();
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertEquals('version 1', $testDoc->getName());

        $this->freezer->thaw($testDoc, $metadata);

        $documentManager->flush();
        $documentManager->clear();
        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertFalse($this->freezer->isFrozen($testDoc, $metadata));
    }

    public function testReadFilter()
    {
        $documentManager = $this->documentManager;

        $extension = $this->extension;
        $extension->setReadFilter(Extension::READ_ONLY_NOT_FROZEN);

        //create some simple, not frozen, documents
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

        //both documents should return, because neither is frozen
        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('lucy', 'miriam'), $docNames);

        //freeze one of the two documents
        if ($testDocs[0]->getName() == 'lucy') {
            $this->freezer->freeze($testDocs[0], $metadata);
        } else {
            $this->freezer->freeze($testDocs[1], $metadata);
        }

        $documentManager->flush();
        $documentManager->clear();

        //only one doc returned, because the other is frozen
        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('miriam'), $docNames);

        $extension->setReadFilter(Extension::READ_ONLY_FROZEN);
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('lucy'), $docNames);

        $extension->setReadFilter(Extension::READ_ONLY_NOT_FROZEN);
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('miriam'), $docNames);

        $extension->setReadFilter(Extension::READ_ALL);

        $documentManager->flush();
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('lucy', 'miriam'), $docNames);

        if ($testDocs[0]->getName() == 'lucy') {
            $this->freezer->thaw($testDocs[0], $metadata);
        } else {
            $this->freezer->thaw($testDocs[1], $metadata);
        }

        $extension->setReadFilter(Extension::READ_ONLY_NOT_FROZEN);

        $documentManager->flush();
        $documentManager->clear();

        list($testDocs, $docNames) = $this->getTestDocs();
        $this->assertEquals(array('lucy', 'miriam'), $docNames);
    }

    protected function getTestDocs()
    {
        $repository = $this->documentManager->getRepository('Zoop\Shard\Test\Freeze\TestAsset\Document\Simple');
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

        $calls = $this->calls;
        $this->assertFalse(isset($calls[Events::PRE_FREEZE]));
        $this->assertFalse(isset($calls[Events::POST_FREEZE]));
        $this->assertFalse(isset($calls[Events::PRE_THAW]));
        $this->assertFalse(isset($calls[Events::POST_THAW]));

        $repository = $documentManager->getRepository(get_class($testDoc));
        $testDoc = $repository->find($id);

        $this->assertFalse($this->freezer->isFrozen($testDoc, $metadata));

        $subscriber->reset();
        $this->freezer->freeze($testDoc, $metadata);

        $documentManager->flush();

        $calls = $this->calls;
        $this->assertTrue(isset($calls[Events::PRE_FREEZE]));
        $this->assertTrue(isset($calls[Events::POST_FREEZE]));
        $this->assertFalse(isset($calls[Events::PRE_THAW]));
        $this->assertFalse(isset($calls[Events::POST_THAW]));

        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertTrue($this->freezer->isFrozen($testDoc, $metadata));

        $testDoc->setName('version 2');
        $subscriber->reset();
        $documentManager->flush();

        $calls = $this->calls;
        $this->assertTrue(isset($calls[Events::FROZEN_UPDATE_DENIED]));
        $subscriber->reset();

        $documentManager->remove($testDoc);
        $documentManager->flush();

        $calls = $this->calls;
        $this->assertTrue(isset($calls[Events::FROZEN_DELETE_DENIED]));

        $documentManager->clear();
        $testDoc = $repository->find($id);

        $subscriber->reset();
        $this->freezer->thaw($testDoc, $metadata);

        $documentManager->flush();

        $calls = $this->calls;
        $this->assertFalse(isset($calls[Events::PRE_FREEZE]));
        $this->assertFalse(isset($calls[Events::POST_FREEZE]));
        $this->assertTrue(isset($calls[Events::PRE_THAW]));
        $this->assertTrue(isset($calls[Events::POST_THAW]));

        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertFalse($this->freezer->isFrozen($testDoc, $metadata));

        $subscriber->reset();
        $subscriber->setRollbackFreeze(true);

        $this->freezer->freeze($testDoc, $metadata);

        $documentManager->flush();

        $calls = $this->calls;
        $this->assertTrue(isset($calls[Events::PRE_FREEZE]));
        $this->assertFalse(isset($calls[Events::POST_FREEZE]));
        $this->assertFalse(isset($calls[Events::PRE_THAW]));
        $this->assertFalse(isset($calls[Events::POST_THAW]));

        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertFalse($this->freezer->isFrozen($testDoc, $metadata));

        $subscriber->reset();
        $this->freezer->freeze($testDoc, $metadata);

        $documentManager->flush();

        $testDoc = null;
        $testDoc = $repository->find($id);

        $this->assertTrue($this->freezer->isFrozen($testDoc, $metadata));

        $subscriber->reset();
        $subscriber->setRollbackThaw(true);
        $this->freezer->thaw($testDoc, $metadata);

        $documentManager->flush();

        $calls = $this->calls;
        $this->assertFalse(isset($calls[Events::PRE_FREEZE]));
        $this->assertFalse(isset($calls[Events::POST_FREEZE]));
        $this->assertTrue(isset($calls[Events::PRE_THAW]));
        $this->assertFalse(isset($calls[Events::POST_THAW]));
    }

    protected $calls = array();

    protected $rollbackFreeze = false;
    protected $rollbackThaw = false;

    public function getSubscribedEvents()
    {
        return array(
            Events::PRE_FREEZE,
            Events::POST_FREEZE,
            Events::PRE_THAW,
            Events::POST_THAW,
            Events::FROZEN_UPDATE_DENIED,
            Events::FROZEN_DELETE_DENIED
        );
    }

    public function reset()
    {
        $this->calls = array();
        $this->rollbackFreeze = false;
        $this->rollbackThaw = false;
    }

    public function preFreeze(FreezerEventArgs $eventArgs)
    {
        $this->calls[Events::PRE_FREEZE] = $eventArgs;
        if ($this->rollbackFreeze) {
            $eventArgs->setReject(true);
        }
    }

    public function preThaw(FreezerEventArgs $eventArgs)
    {
        $this->calls[Events::PRE_THAW] = $eventArgs;
        if ($this->rollbackThaw) {
            $eventArgs->setReject(true);
        }
    }

    public function getRollbackFreeze()
    {
        return $this->rollbackFreeze;
    }

    public function setRollbackFreeze($rollbackFreeze)
    {
        $this->rollbackFreeze = $rollbackFreeze;
    }

    public function getRollbackThaw()
    {
        return $this->rollbackThaw;
    }

    public function setRollbackThaw($rollbackThaw)
    {
        $this->rollbackThaw = $rollbackThaw;
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
