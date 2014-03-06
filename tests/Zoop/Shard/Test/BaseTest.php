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
        global $metadataCacheOnly;

        if ($result === NULL) {
            $result = $this->createResult();
        }

        /**
         * Run the testsuite multiple times with different metadata cache
         */
        $runs = [
            [
                'metadataCache' => false,
                'message'       => 'Array cache     : '
            ],
            [
                'metadataCache' => 'doctrine.cache.juggernaut',
                'message'       => 'Juggernaut cache: '
            ]
        ];

        foreach ($runs as $run) {
            if (!isset($metadataCacheOnly) || $run['metadataCache'] == $metadataCacheOnly) {
                print $run['message'];
                $this->metadataCache = $run['metadataCache'];

                //Note: The cache has to be cleared out and re-primed before each test run.
                //Di
                //clearout metadata cache first
                array_map('unlink', glob( __DIR__ . '/../../../Metadata/*.php'));

                //first test run will prime the cache
                $result->run($this);

                //if the cache has been primed, run a second time
                if ( count(glob( __DIR__ . '/../../../Metadata/*.php')) > 0) {
                    $result->run($this);
                }

                print PHP_EOL;
            }
        }

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
