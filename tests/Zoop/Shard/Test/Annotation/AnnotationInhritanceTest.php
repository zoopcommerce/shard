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
                'documents' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.serializer' => true,
                    'extension.validator' => true,
                ],
                'document_manager' => 'testing.documentmanager',
                'service_manager_config' => [
                    'factories' => [
                        'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                        'user' => function () {
                            $user = new User();
                            return $user;
                        }
                    ]
                ]
           ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');
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
