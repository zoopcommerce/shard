<?php

namespace Zoop\Shard\Test;

use Zoop\Shard\Test\TestAsset\DocumentManagerFactory;

abstract class BaseTest extends \PHPUnit_Framework_TestCase
{

    protected $documentManager;

    public function tearDown()
    {
        if ($this->documentManager) {
            $collections = $this->documentManager->getConnection()->selectDatabase(DocumentManagerFactory::DEFAULT_DB)->listCollections();
            foreach ($collections as $collection) {
                $collection->remove(array(), array('safe' => true));
            }
        }
    }
}