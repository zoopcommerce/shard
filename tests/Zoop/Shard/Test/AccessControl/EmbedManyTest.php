<?php

namespace Zoop\Shard\Test\AccessControl;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\AccessControl\Events as AccessControlEvents;
use Zoop\Shard\Test\TestAsset\RoleAwareUser;
use Zoop\Shard\Test\AccessControl\TestAsset\Document\Album;
use Zoop\Shard\Test\AccessControl\TestAsset\Document\Artist;
use Zoop\Shard\Test\AccessControl\TestAsset\Document\Mechanical;
use Zoop\Shard\Test\AccessControl\TestAsset\Document\Licensing;

use Zoop\Shard\Test\AccessControl\TestAsset\Document\SongWriter;

class EmbedManyTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'models' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.accessControl' => true,
                    'extension.odmcore' => true
                ],
                'service_manager_config' => [
                    'factories' => [
                        'user' => function () {
                            $user = new RoleAwareUser();
                            $user->setUsername('john');
                            $user->addRole('guest');

                            return $user;
                        }
                    ]
                ]
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
    }

    public function testDiscriminatorField()
    {
        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(AccessControlEvents::CREATE_DENIED, $this);

        $name = 'Abbey Road';
        $testDoc = new Album($name);
        $testDoc->addArtist(new Artist('The Beatles'));

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $documentManager->clear();

        $testDoc = $documentManager->getRepository(get_class($testDoc))->find($testDoc->getId());

        $this->assertCount(1, $testDoc->getArtists());
        $this->assertFalse(isset($this->calls[AccessControlEvents::CREATE_DENIED]));
    }

    public function testDiscriminatorMap()
    {
        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(AccessControlEvents::CREATE_DENIED, $this);

        $name = 'Let It Be';
        $testDoc = new Album($name);
        $testDoc->addArtist(new Artist('The Beatles'));
        $testDoc->addSongWriter(new Artist('The Beatles'));
        $testDoc->addSongWriter(new SongWriter('John'));
        $testDoc->addSongWriter(new SongWriter('Paul'));

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $documentManager->clear();

        $testDoc = $documentManager->getRepository(get_class($testDoc))->find($testDoc->getId());

        $this->assertCount(1, $testDoc->getArtists());
        $this->assertCount(3, $testDoc->getSongWriters());
        $this->assertFalse(isset($this->calls[AccessControlEvents::CREATE_DENIED]));
    }

    public function testDiscriminatorDenyMap()
    {
        $this->calls = array();
        $documentManager = $this->documentManager;
        $eventManager = $documentManager->getEventManager();

        $eventManager->addEventListener(AccessControlEvents::CREATE_DENIED, $this);

        $name = 'Let It Be';
        $testDoc = new Album($name);
        $testDoc->addArtist(new Artist('The Beatles'));
        $testDoc->addSongWriter(new Artist('The Beatles'));
        $testDoc->addSongWriter(new SongWriter('John'));
        $testDoc->addSongWriter(new SongWriter('Paul'));
        $testDoc->addRoyalty(new Mechanical('Mushroom', 10000));
        $testDoc->addRoyalty(new Licensing('Mushroom', 10000));

        $documentManager->persist($testDoc);
        $documentManager->flush();
        $documentManager->clear();

        $testDoc = $documentManager->getRepository(get_class($testDoc))->find($testDoc->getId());

        $this->assertCount(1, $testDoc->getArtists());
        $this->assertCount(3, $testDoc->getSongWriters());
        $this->assertCount(0, $testDoc->getRoyalties());
        $this->assertTrue(isset($this->calls[AccessControlEvents::CREATE_DENIED]));
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }
}
