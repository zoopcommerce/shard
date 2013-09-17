<?php

namespace Zoop\Shard\Test\Serializer;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Birthday;

class SerializerDateTest extends BaseTest
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
                    'extension.odmcore' => true
                ],
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('objectmanager');
        $this->serializer = $manifest->getServiceManager()->get('serializer');
        $this->unserializer = $manifest->getServiceManager()->get('unserializer');
    }

    public function testSerializerMongoDate()
    {
        $birthday = new Birthday('Miriam', new \MongoDate(strtotime('1950-01-01 Europe/Berlin')));

        $correct = [
            'name' => 'Miriam',
            'date' => '1949-12-31T23:00:00+00:00'
        ];

        $array = $this->serializer->toArray($birthday, $this->documentManager);

        $this->assertEquals($correct, $array);
    }

    public function testSerializerDateTime()
    {
        $birthday = new Birthday('Miriam', new \DateTime('01/01/1950', new \DateTimeZone('Europe/Berlin')));

        $correct = [
            'name' => 'Miriam',
            'date' => '1949-12-31T23:00:00+00:00'
        ];

        $array = $this->serializer->toArray($birthday, $this->documentManager);

        $this->assertEquals($correct, $array);
    }

    public function testUnserializer()
    {
        $data = array(
            'name' => 'Miriam',
            'date' => '1949-12-31T23:00:00+00:00'
        );

        $birthday = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\Birthday'
        );

        $this->assertTrue($birthday instanceof Birthday);
        $this->assertTrue($birthday->getDate() instanceof \DateTime);

        $birthday->getDate()->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $this->assertEquals('01/01/1950', $birthday->getDate()->format('d/m/Y'));
    }
}
