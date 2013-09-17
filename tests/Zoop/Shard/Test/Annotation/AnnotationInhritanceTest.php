<?php

namespace Zoop\Shard\Test\Annotation;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\Annotation\TestAsset\Document\ChildA;
use Zoop\Shard\Test\Annotation\TestAsset\Document\ChildB;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\TestAsset\User;

class AnnotationInheritaceTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'object_map' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.serializer' => true,
                    'extension.validator' => true,
                    'extension.odmcore' => true
                ],
                'service_manager_config' => [
                    'factories' => [
                        'user' => function () {
                            $user = new User();

                            return $user;
                        }
                    ]
                ]
           ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('objectmanager');
    }

    public function testAnnotationInheritance()
    {
        $documentManager = $this->documentManager;

        $metadata = $documentManager->getClassMetadata(get_class(new ChildA));

        $this->assertEquals(['class' => 'ParentValidator', 'options' => []], $metadata->validator['document']);
        $this->assertEquals(true, $metadata->serializer['fields']['name']['serializeIgnore']);
    }

    public function testAnnotationInheritanceOverride()
    {
        $documentManager = $this->documentManager;

        $metadata = $documentManager->getClassMetadata(get_class(new ChildB));

        $this->assertEquals(['class' => 'ChildBValidator', 'options' => []], $metadata->validator['document']);
        $this->assertEquals(false, $metadata->serializer['fields']['name']['serializeIgnore']);
    }
}
