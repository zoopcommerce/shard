<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document\Order;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\EmbeddedDocument
 */
class Dimensions
{
    /**
     *
     * @ODM\Float
     */
    protected $weight;

    /**
     *
     * @ODM\Float
     */
    protected $width;

    /**
     *
     * @ODM\Float
     */
    protected $height;

    /**
     *
     * @ODM\Float
     */
    protected $depth;

    /**
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     *
     * @param float $weight
     */
    public function setWeight($weight)
    {
        $this->weight = (float) $weight;
    }

    /**
     *
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     *
     * @param float $width
     */
    public function setWidth($width)
    {
        $this->width = (float) $width;
    }

    /**
     *
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     *
     * @param float $height
     */
    public function setHeight($height)
    {
        $this->height = (float) $height;
    }

    /**
     *
     * @return float
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     *
     * @param float $depth
     */
    public function setDepth($depth)
    {
        $this->depth = (float) $depth;
    }
}
