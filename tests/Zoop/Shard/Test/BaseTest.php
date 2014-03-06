<?php

namespace Zoop\Shard\Test;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_TestResult;

abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    protected $metadataCache;

    protected $documentManager;

    public function run(PHPUnit_Framework_TestResult $result = NULL)
    {
        if ($result === NULL) {
            $result = $this->createResult();
        }

        /**
         * Run the testsuite multiple times with different metadata cache
         */
        print "Array cache: ";
        $this->metadataCache = false;
        $result->run($this);
        print PHP_EOL;

        print "Juggernaut cache: ";
        $this->metadataCache = 'doctrine.cache.juggernaut';
        $result->run($this);
        print PHP_EOL;

        return $result;
    }

    public function tearDown()
    {
        if ($this->documentManager) {
            $collections = $this->documentManager
                ->getConnection()
                ->selectDatabase('zoop-shard')->listCollections();

            foreach ($collections as $collection) {
                $collection->remove(array(), array('safe' => true));
            }
        }
    }

    protected function getOdmCoreConfig()
    {
        $config = [
            'proxy_dir' => __DIR__ . '/../../../Proxies',
            'hydrator_dir' => __DIR__ . '/../../../Hydrators'
        ];

        if ($this->metadataCache) {
            $config['metadata_cache'] = $this->metadataCache;
            $config['metadata_cache_dir'] = __DIR__ . '/../../../Metadata';
        }

        return $config;
    }
}
