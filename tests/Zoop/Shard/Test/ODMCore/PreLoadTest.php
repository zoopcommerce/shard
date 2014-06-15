<?php

namespace Zoop\Shard\Test\ODMCore;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\ODMCore\TestAsset\Document\Album;
use Zoop\Shard\Test\ODMCore\TestAsset\Document\Artist;
use Zoop\Shard\Test\ODMCore\TestAsset\Document\RecordLabel;
use Zoop\Shard\Test\ODMCore\TestAsset\Document\Song;
use Zoop\Shard\Test\BaseTest;

class PreLoadTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'models' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.odmcore' => true
                ],
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
    }

    public function testEmbedOneWithDiscriminatorMap()
    {
        $license1 = new Artist('Pearl Jam');
        $license2 = new RecordLabel('Epic Records');

        //create and persist a document
        $document = new Album('Ten');
        $document->addSong(new Song('Once', $license1));
        $document->addSong(new Song('Even Flow', $license2));
        $document->addSong(new Song('Alive', $license2));

        $this->documentManager->persist($document);
        $this->documentManager->flush();
        $this->documentManager->clear();
        $id = $document->getId();
        unset($document);
        //get the proxy
        $document = $this->documentManager
            ->getRepository('Zoop\Shard\Test\ODMCore\TestAsset\Document\Album')
            ->find($id);

        $this->assertTrue($document instanceof Album);
        $this->assertEquals('Ten', $document->getName());

        $songs = $document->getSongs();
        $this->assertCount(3, $songs);

        $song = $songs[0];
        $this->assertTrue($song instanceof Song);
        $this->assertEquals('Once', $song->getName());
        $license = $song->getLicense();
        $this->assertTrue($license instanceof Artist);
        $this->assertEquals('Pearl Jam', $license->getName());

        $song = $songs[1];
        $this->assertTrue($song instanceof Song);
        $this->assertEquals('Even Flow', $song->getName());
        $license = $song->getLicense();
        $this->assertTrue($license instanceof RecordLabel);
        $this->assertEquals('Epic Records', $license->getName());
    }
}
