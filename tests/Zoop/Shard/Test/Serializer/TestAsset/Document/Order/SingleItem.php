<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document\Order;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\EmbeddedDocument
 */
class SingleItem extends AbstractItem
{
    /**
     *
     * @ODM\String
     */
    protected $name;
    
    /**
     * @ODM\EmbedOne(
     *      discriminatorField="type",
     *      discriminatorMap={
     *         "PhysicalSku"    = "PhysicalSku",
     *         "DigitalSku"     = "DigitalSku"
     *      }
     * )
     */
    protected $sku;

    /**
     * @return PhysicalSku|DigitalSku
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param PhysicalSku|DigitalSku $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }
    
    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
