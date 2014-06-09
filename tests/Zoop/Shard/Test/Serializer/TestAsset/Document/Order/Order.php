<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document\Order;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 */
class Order
{
    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     *
     * @ODM\String
     */
    protected $name;

    /**
     *
     * @ODM\EmbedMany(
     *     discriminatorField="type",
     *     discriminatorMap={
     *         "SingleItem"     = "SingleItem",
     *         "Bundle"         = "Bundle"
     *     }
     * )
     */
    protected $items;

    /**
     *
     * @ODM\EmbedOne(targetDocument="Total")
     */
    protected $total;
    
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     *
     * @return Total
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     *
     * @param Total $total
     */
    public function setTotal(Total $total)
    {
        $this->total = $total;
    }
}
