<?php

namespace Zoop\Shard\Test\Serializer;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Serializer\TestAsset\Document\CakeWithSecrets;
use Zoop\Shard\Test\Serializer\TestAsset\Document\SecretIngredient;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Ingredient;

class AccessControlledReferenceDenyTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'documents' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.accessControl' => true,
                    'extension.serializer' => true
                ],
                'document_manager' => 'testing.documentmanager',
                'service_manager_config' => [
                    'factories' => [
                        'testing.documentmanager' => 'Zoop\Shard\Test\TestAsset\DocumentManagerFactory',
                    ]
                ]
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('testing.documentmanager');
        $this->serializer = $manifest->getServiceManager()->get('serializer');
    }

    public function testSerializeAllow()
    {
        $documentManager = $this->documentManager;

        //bake the cake. Hmm yum.
        $cake = new CakeWithSecrets();
        $cake->setIngredients(
            [
                new Ingredient('flour'),
                new Ingredient('sugar')
            ]
        );

        $chocolate = new SecretIngredient('chocolate');
        $strawberry = new SecretIngredient('strawberry');

        $cake->setSecretIngredients(
            [
                $chocolate,
                $strawberry
            ]
        );

        //Persist cake and clear out documentManager
        $documentManager->persist($cake);
        $documentManager->flush();
        $id = $cake->getId();
        $documentManager->clear();

        $cake = $documentManager
            ->getRepository('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeWithSecrets')
            ->findOneBy(['id' => $id]);

        $this->serializer->setMaxNestingDepth(1);
        $array = $this->serializer->toArray($cake, $documentManager);

        $this->assertCount(2, $array['ingredients']);
        $this->assertFalse(isset($array['secretIngredients']));

    }
}
