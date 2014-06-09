<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document\Order;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\EmbeddedDocument
 */
class PhysicalSku
{
    /**
     * @ODM\EmbedOne(targetDocument="Dimensions")
     */
    protected $dimensions;

    /**
     * @return Dimensions
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * @param Dimensions $dimensions
     */
    public function setDimensions(Dimensions $dimensions)
    {
        $this->dimensions = $dimensions;
    }
}
