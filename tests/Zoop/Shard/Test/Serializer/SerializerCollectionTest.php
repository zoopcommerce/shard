<?php

namespace Zoop\Shard\Test\Serializer;

use \ArrayObject;
use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Post;

class SerializerCollectionTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest([
            'models' => [
                __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
            ],
            'extension_configs' => [
                'extension.serializer' => true,
                'extension.odmcore' => $this->getOdmCoreConfig()
            ],
        ]);

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
        $this->serializer = $manifest->getServiceManager()->get('serializer');
        $this->unserializer = $manifest->getServiceManager()->get('unserializer');
    }

    public function testSerializerCollection()
    {
        $title = 'Justin Bieber arrested again...oh noes';
        $tags = [
            'Justin Bieber',
            'Not News'
        ];
        $post = new Post();
        $post->setTitle($title);
        $post->addTag($tags[0]);
        $post->addTag($tags[1]);
        $post->addArrayTag($tags[0]);
        $post->addArrayTag($tags[1]);
        $post->addArrayObjectTag($tags[0]);
        $post->addArrayObjectTag($tags[1]);
        $post->addArrayCollectionTag($tags[0]);
        $post->addArrayCollectionTag($tags[1]);

        $correct = [
            'title' => $title,
            'tags' => $tags,
            'arrayTags' => $tags,
            'arrayObjectTags' => $tags,
            'arrayCollectionTags' => $tags
        ];

        $array = $this->serializer->toArray($post, $this->documentManager);

        $this->assertEquals($correct, $array);
    }

    public function testUnserializeCollection()
    {
        $title = 'Justin Bieber arrested again...oh noes';
        $tags = [
            'Justin Bieber',
            'Not News'
        ];

        $data = [
            'title' => $title,
            'tags' => $tags,
            'arrayTags' => $tags,
            'arrayObjectTags' => $tags,
            'arrayCollectionTags' => $tags
        ];

        $post = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\Post'
        );

        $this->assertTrue($post instanceof Post);
        
        $this->assertTrue($post->getArrayCollectionTags() instanceof ArrayCollection);
        $this->assertTrue($post->getArrayObjectTags() instanceof ArrayObject);
        $this->assertFalse($post->getArrayTags() instanceof ArrayCollection);
        $this->assertTrue(is_array($post->getArrayTags()));
        $this->assertTrue(is_array($post->getTags()));
        
        $this->assertCount(2, $post->getArrayCollectionTags());
        $this->assertCount(2, $post->getArrayObjectTags());

        $post->addArrayCollectionTag('Who cares');

        $this->assertCount(3, $post->getArrayCollectionTags());
    }
}
