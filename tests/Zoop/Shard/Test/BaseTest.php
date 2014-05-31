<?php

namespace Zoop\Shard\Test;

abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    protected $documentManager;

    public function tearDown()
    {
        if ($this->documentManager) {
            $collections = $this->documentManager
                ->getConnection()
                ->selectDatabase('zoop-shard')->listCollections();

            foreach ($collections as $collection) {
                $collection->remove();
            }
        }
    }
}
