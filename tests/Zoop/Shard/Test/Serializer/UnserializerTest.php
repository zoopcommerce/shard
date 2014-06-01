<?php

namespace Zoop\Shard\Test\Serializer;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\Serializer\TestAsset\Document\User;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Order\Order;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Order\SingleItem;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Order\Bundle;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Order\PhysicalSku;

class UnserializerTest extends BaseTest
{
    public function setUp()
    {
        $manifest = new Manifest(
            [
                'models' => [
                    __NAMESPACE__ . '\TestAsset\Document' => __DIR__ . '/TestAsset/Document'
                ],
                'extension_configs' => [
                    'extension.serializer' => true,
                    'extension.odmcore' => true
                ],
            ]
        );

        $this->documentManager = $manifest->getServiceManager()->get('modelmanager');
        $this->unserializer = $manifest->getServiceManager()->get('unserializer');
    }

    public function testUnserializer()
    {
        $data = array(
            'id' => 1234567890,
            'username' => 'superdweebie',
            'password' => 'testIgnore',
            'location' => 'here',
            'groups' => array(
                array('name' => 'groupA'),
                array('name' => 'groupB'),
            ),
            'profile' => array(
                'firstname' => 'Tim',
                'lastname' => 'Roediger'
            ),
        );

        $user = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\User'
        );

        $this->assertTrue($user instanceof User);
        $this->assertEquals(1234567890, $user->getId());
        $this->assertEquals('superdweebie', $user->getUsername());
        $this->assertEquals(null, $user->getPassword());
        $this->assertEquals('here', $user->location());
        $this->assertEquals('groupA', $user->getGroups()[0]->getName());
        $this->assertEquals('groupB', $user->getGroups()[1]->getName());
        $this->assertEquals('Tim', $user->getProfile()->getFirstname());
        $this->assertEquals('Roediger', $user->getProfile()->getLastname());
    }

    public function testNestedAbstractEmbedUnserializer()
    {
        $data = array(
            'name' => 'crimsonronin',
            'items' => array(
                array(
                    'type' => 'SingleItem',
                    'name' => 'LG 22" Monitor',
                    'price' => array(
                        'wholesale' => 150,
                        'list' => 200,
                        'subTotal' => 200,
                        'discount' => 10,
                        'total' => 190,
                        'taxIncluded' => 10,
                        'shipping' => 10,
                    ),
                    'sku' => array(
                        'type' => 'PhysicalSku',
                        'dimensions' => array(
                            'width' => 22,
                            'height' => 30,
                            'depth' => 5,
                            'weight' => 10,
                        )
                    )
                ),
                array(
                    'type' => 'Bundle',
                    'name' => 'Logitech Keyboard',
                    'price' => array(
                        'wholesale' => 10,
                        'list' => 50,
                        'subTotal' => 50,
                        'discount' => 0,
                        'total' => 50,
                        'taxIncluded' => 5,
                        'shipping' => 2,
                    )
                )
            ),
            'total' => array(
                'shippingPrice' => 12,
                'productWholesalePrice' => 160,
                'productListPrice' => 250,
                'productQuantity' => 2,
                'discountPrice' => 10,
                'taxIncluded' => 15,
                'orderPrice' => 252
            )
        );

        $order = $this->unserializer->fromArray(
            $data,
            'Zoop\Shard\Test\Serializer\TestAsset\Document\Order\Order'
        );
        
        $this->assertTrue($order instanceof Order);
        $this->assertEquals('crimsonronin', $order->getName());
        $this->assertCount(2, $order->getItems());
        
        $single = $order->getItems()[0];
        $this->assertTrue($single instanceof SingleItem);
        $this->assertEquals(150, $single->getPrice()->getWholesale());
        $this->assertTrue($single->getSku() instanceof PhysicalSku);
        
        $bundle = $order->getItems()[1];
        $this->assertTrue($bundle instanceof Bundle);
        $this->assertEquals(10, $bundle->getPrice()->getWholesale());
    }
}
