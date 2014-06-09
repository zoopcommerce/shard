<?php

namespace Zoop\Shard\Test\ODMCore;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\ODMCore\TestAsset\Document\Album;

class MultipleConnectionTest extends BaseTest
{
    public function testMultipleConnections()
    {
        $manifest1 = new Manifest(
            [
                'models' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.odmcore'   => [
                        'default_db' => 'shard-phpunit-1'
                    ],
                ],
            ]
        );

        $manifest2 = new Manifest(
            [
                'models' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.odmcore'   => [
                        'default_db' => 'shard-phpunit-2'
                    ],
                ],
            ]
        );

        $documentManager1 = $manifest1->getServiceManager()->get('modelmanager');
        $documentManager2 = $manifest2->getServiceManager()->get('modelmanager');

        $album1 = new Album('Ivy and the big apples');
        $album2 = new Album('Guide to better living');

        $documentManager1->persist($album1);
        $documentManager2->persist($album2);

        $documentManager1->flush();
        $documentManager2->flush();

        $albums1 = $documentManager1->getRepository(get_class($album1))->findAll();
        $this->assertCount(1, $albums1);
        $this->assertEquals('Ivy and the big apples', $albums1[0]->getName());

        $albums2 = $documentManager2->getRepository(get_class($album2))->findAll();
        $this->assertCount(1, $albums2);
        $this->assertEquals('Guide to better living', $albums2[0]->getName());

        //cleanup
        $collections = $documentManager1->getConnection()->selectDatabase('shard-phpunit-1')->listCollections();
        foreach ($collections as $collection) {
            $collection->remove();
        }

        $collections = $documentManager2->getConnection()->selectDatabase('shard-phpunit-2')->listCollections();

        foreach ($collections as $collection) {
            $collection->remove();
        }
    }
}
