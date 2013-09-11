<?php

namespace Zoop\Shard\Test\Serializer;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Serializer\TestAsset\Document\CakeEager;
use Zoop\Shard\Test\Serializer\TestAsset\Document\CakeEagerSimpleReference;
use Zoop\Shard\Test\Serializer\TestAsset\Document\CakeRefLazy;
use Zoop\Shard\Test\Serializer\TestAsset\Document\CakeSimpleLazySimpleReference;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Flavour;
use Zoop\Shard\Test\Serializer\TestAsset\Document\FlavourEager;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Ingredient;

class SerializerReferenceTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'documents' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
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
        $this->unserializer = $manifest->getServiceManager()->get('unserializer');
    }

    public function testEagerSerializer()
    {
        $documentManager = $this->documentManager;

        //bake the eager cake. Hmm yum.
        $cake = new CakeEager();
        $cake->setIngredients(
            [
                $this->createIngredient('flour'),
                $this->createIngredient('sugar'),
                $this->createIngredient('water'),
                $this->createIngredient('eggs')
            ]
        );

        $flavour = new FlavourEager('chocolate');
        $documentManager->persist($flavour);
        $cake->setFlavour($flavour);

        //Persist cake and clear out documentManager
        $documentManager->persist($cake);
        $documentManager->flush();
        $id = $cake->getId();
        $documentManager->clear();

        $cake = $documentManager
            ->getRepository('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeEager')
            ->findOneBy(['id' => $id]);

        $this->serializer->setMaxNestingDepth(1);
        $array = $this->serializer->toArray($cake);

        $this->assertCount(4, $array['ingredients']);
        $this->assertEquals('flour', $array['ingredients'][0]['name']);
        $this->assertEquals('chocolate', $array['flavour']['name']);

        // maxNestingDepth = 1 should not display cakes
        $this->assertArrayNotHasKey('cakes', $array['flavour']);

        $this->serializer->setMaxNestingDepth(2);
        $array = $this->serializer->toArray($cake);

        // maxNestingDepth = 2 should display cakes
        $this->assertArrayHasKey('cakes', $array['flavour']);

        $array['ingredients'][3] = ['name' => 'coconut'];
        $cake = $this->unserializer->fromArray(
            $array,
            $documentManager->getClassMetadata('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeEager')
        );

        $this->assertInstanceOf('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeEager', $cake);
        $this->assertEquals('chocolate', $cake->getFlavour()->getName());
        $this->assertCount(4, $cake->getIngredients());
        $this->assertEquals('coconut', $cake->getIngredients()[3]->getName());
    }

    public function testRefLazySerializer()
    {
        $documentManager = $this->documentManager;

        //bake the lazy cake. Hmm yum.
        $cake = new CakeRefLazy();
        $cake->setIngredients(
            [
                $this->createIngredient('flour'),
                $this->createIngredient('sugar'),
                $this->createIngredient('water'),
                $this->createIngredient('eggs')
            ]
        );

        $flavour = new Flavour('carrot');
        $documentManager->persist($flavour);
        $cake->setFlavour($flavour);

        //Persist cake and clear out documentManager
        $documentManager->persist($cake);
        $documentManager->flush();
        $id = $cake->getId();
        $documentManager->clear();

        $cake = $documentManager
            ->getRepository('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeRefLazy')
            ->findOneBy(['id' => $id]);

        $array = $this->serializer->toArray($cake);

        $this->assertCount(4, $array['ingredients']);
        $this->assertArrayHasKey('$ref', $array['ingredients'][0]);
        $pieces = explode('/', $array['ingredients'][0]['$ref']);
        $this->assertCount(2, $pieces);
        $this->assertEquals('Ingredient', $pieces[0]);

        $this->assertArrayHasKey('$ref', $array['flavour']);
        $pieces = explode('/', $array['flavour']['$ref']);
        $this->assertCount(2, $pieces);
        $this->assertEquals('Flavour', $pieces[0]);


        $array['ingredients'][3] = ['name' => 'coconut'];
        $cake = $this->unserializer->fromArray(
            $array,
            $documentManager->getClassMetadata('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeRefLazy')
        );

        $this->assertInstanceOf('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeRefLazy', $cake);
        $this->assertEquals('carrot', $cake->getFlavour()->getName());
        $this->assertCount(4, $cake->getIngredients());
        $this->assertEquals('water', $cake->getIngredients()[2]->getName());
        $this->assertEquals('coconut', $cake->getIngredients()[3]->getName());
    }

    public function testEagerApplyToArray()
    {
        $documentManager = $this->documentManager;

        //bake the cake. Hmm yum.
        $cake = new CakeEager();
        $cake->setIngredients(
            [
                $this->createIngredient('flour'),
                $this->createIngredient('sugar'),
                $this->createIngredient('water'),
                $this->createIngredient('eggs')
            ]
        );

        $flavour = new FlavourEager('black_forest');
        $documentManager->persist($flavour);
        $cake->setFlavour($flavour);

        //Persist cake and clear out documentManager
        $documentManager->persist($cake);
        $documentManager->flush();
        $id = $cake->getId();
        $documentManager->clear();

        $cakeArray = $documentManager
            ->createQueryBuilder()
            ->find('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeEager')
            ->field('id')->equals($id)
            ->hydrate(false)
            ->getQuery()
            ->getSingleResult();

        $array = $this->serializer->applySerializeMetadataToArray(
            $cakeArray,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\CakeEager'
        );

        $this->assertCount(4, $array['ingredients']);
        $this->assertEquals('flour', $array['ingredients'][0]['name']);
        $this->assertEquals('black_forest', $array['flavour']['name']);
    }

    public function testRefLazyApplyToArray()
    {
        $documentManager = $this->documentManager;

        //bake the cake. Hmm yum.
        $cake = new CakeRefLazy();
        $cake->setIngredients(
            [
                $this->createIngredient('flour'),
                $this->createIngredient('sugar'),
                $this->createIngredient('water'),
                $this->createIngredient('eggs')
            ]
        );

        $flavour = new Flavour('carrot');
        $documentManager->persist($flavour);
        $cake->setFlavour($flavour);

        //Persist cake and clear out documentManager
        $documentManager->persist($cake);
        $documentManager->flush();
        $id = $cake->getId();
        $documentManager->clear();

        $cakeArray = $documentManager
            ->createQueryBuilder()
            ->find('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeRefLazy')
            ->field('id')->equals($id)
            ->hydrate(false)
            ->getQuery()
            ->getSingleResult();

        $array = $this->serializer->applySerializeMetadataToArray(
            $cakeArray,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\CakeRefLazy'
        );

        $this->assertCount(4, $array['ingredients']);
        $this->assertArrayHasKey('$ref', $array['ingredients'][0]);
        $pieces = explode('/', $array['ingredients'][0]['$ref']);
        $this->assertCount(2, $pieces);
        $this->assertEquals('Ingredient', $pieces[0]);

        $this->assertArrayHasKey('$ref', $array['flavour']);
        $pieces = explode('/', $array['flavour']['$ref']);
        $this->assertCount(2, $pieces);
        $this->assertEquals('Flavour', $pieces[0]);
    }


    public function testEagerSerializerWithNull()
    {
        $documentManager = $this->documentManager;

        //bake the eager cake. Hmm yum.
        $cake = new CakeEager();

        //Persist cake and clear out documentManager
        $documentManager->persist($cake);
        $documentManager->flush();
        $id = $cake->getId();
        $documentManager->clear();

        $cake = $documentManager
            ->getRepository('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeEager')
            ->findOneBy(['id' => $id]);

        $array = $this->serializer->toArray($cake);

        $this->assertArrayNotHasKey('ingredients', $array);
        $this->assertArrayNotHasKey('flavour', $array);

    }

    public function testEagerSerializerWithSimpleReference()
    {
        $documentManager = $this->documentManager;

        //bake the eager cake. Hmm yum.
        $cake = new CakeEagerSimpleReference();
        $cake->setIngredients(
            [
                $this->createIngredient('flour'),
                $this->createIngredient('sugar'),
                $this->createIngredient('water'),
                $this->createIngredient('eggs')
            ]
        );

        $flavour = new FlavourEager('chocolate');
        $documentManager->persist($flavour);
        $cake->setFlavour($flavour);

        //Persist cake and clear out documentManager
        $documentManager->persist($cake);
        $documentManager->flush();
        $id = $cake->getId();
        $documentManager->clear();

        $cake = $documentManager
            ->getRepository('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeEagerSimpleReference')
            ->findOneBy(['id' => $id]);

        $array = $this->serializer->toArray($cake);

        $this->assertCount(4, $array['ingredients']);
        $this->assertEquals('flour', $array['ingredients'][0]['name']);
        $this->assertEquals('chocolate', $array['flavour']['name']);

        $array['ingredients'][3] = ['name' => 'coconut'];
        $cake = $this->unserializer->fromArray(
            $array,
            $documentManager->getClassMetadata('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeEagerSimpleReference')
        );

        $this->assertInstanceOf('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeEagerSimpleReference', $cake);
        $this->assertEquals('chocolate', $cake->getFlavour()->getName());
        $this->assertCount(4, $cake->getIngredients());
        $this->assertEquals('coconut', $cake->getIngredients()[3]->getName());
    }

    public function testSimpleLazySerializerWithSimpleReference()
    {
        $documentManager = $this->documentManager;

        //bake the cake. Hmm yum.
        $cake = new CakeSimpleLazySimpleReference();
        $cake->setIngredients(
            [
                $this->createIngredient('flour'),
                $this->createIngredient('sugar'),
                $this->createIngredient('water'),
                $this->createIngredient('eggs')
            ]
        );

        $flavour = new Flavour('carrot');
        $documentManager->persist($flavour);
        $cake->setFlavour($flavour);

        //Persist cake and clear out documentManager
        $documentManager->persist($cake);
        $documentManager->flush();
        $id = $cake->getId();
        $documentManager->clear();

        $cakeArray = $documentManager
            ->createQueryBuilder()
            ->find('Zoop\Shard\Test\Serializer\TestAsset\Document\CakeSimpleLazySimpleReference')
            ->field('id')->equals($id)
            ->hydrate(false)
            ->getQuery()
            ->getSingleResult();

        $array = $this->serializer->applySerializeMetadataToArray(
            $cakeArray,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\CakeSimpleLazySimpleReference'
        );

        $this->assertCount(4, $array['ingredients']);
        $pieces = explode('/', $array['ingredients'][0]);
        $this->assertCount(2, $pieces);
        $this->assertEquals('Ingredient', $pieces[0]);

        $pieces = explode('/', $array['flavour']);
        $this->assertCount(2, $pieces);
        $this->assertEquals('Flavour', $pieces[0]);
    }

    protected function createIngredient($name)
    {
        $ingredient = new Ingredient($name);
        $this->documentManager->persist($ingredient);
        return $ingredient;
    }
}
