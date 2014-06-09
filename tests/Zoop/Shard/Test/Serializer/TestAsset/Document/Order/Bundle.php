<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document\Order;

use Doctrine\Common\Collections\ArrayCollection;
use Zoop\Shard\Test\Serializer\TestAsset\Document\Order\SingleItem;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\EmbeddedDocument
 */
class Bundle
{
    /**
     * @ODM\String
     * @Shard\Validator\Required
     */
    protected $name;

    /**
     * @ODM\EmbedOne(targetDocument="Price")
     */
    protected $price;
    
    /**
     *
     * @ODM\EmbedMany(targetDocument="SingleItem")
     */
    protected $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @param AbstractItem $item
     */
    public function addItem(SingleItem $item)
    {
        $this->getItems()->add($item);
    }

    /**
     * @return ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param ArrayCollection $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param Price $price
     */
    public function setPrice(Price $price)
    {
        $this->price = $price;
    }
}
