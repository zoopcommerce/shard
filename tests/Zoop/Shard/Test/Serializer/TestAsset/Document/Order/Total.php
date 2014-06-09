<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document\Order;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\EmbeddedDocument
 */
class Total
{
    /**
     *
     * @ODM\Float
     */
    protected $shippingPrice;

    /**
     *
     * @ODM\Float
     */
    protected $productWholesalePrice;

    /**
     *
     * @ODM\Float
     */
    protected $productListPrice;

    /**
     *
     * @ODM\Int
     */
    protected $productQuantity;

    /**
     *
     * @ODM\Float
     */
    protected $discountPrice;

    /**
     *
     * @ODM\Float
     */
    protected $taxIncluded;

    /**
     *
     * @ODM\Float
     */
    protected $orderPrice;

    /**
     *
     * @return float
     */
    public function getShippingPrice()
    {
        return $this->shippingPrice;
    }

    /**
     *
     * @param float $shippingPrice
     */
    public function setShippingPrice($shippingPrice)
    {
        $this->shippingPrice = (float) $shippingPrice;
    }

    /**
     * @return float
     */
    public function getProductWholesalePrice()
    {
        return $this->productWholesalePrice;
    }

    /**
     * @param float $productWholesalePrice
     */
    public function setProductWholesalePrice($productWholesalePrice)
    {
        $this->productWholesalePrice = (float) $productWholesalePrice;
    }

    /**
     * @return float
     */
    public function getProductListPrice()
    {
        return $this->productListPrice;
    }

    /**
     * @param float $productListPrice
     */
    public function setProductListPrice($productListPrice)
    {
        $this->productListPrice = $productListPrice;
    }

    /**
     * @return float
     */
    public function getTaxIncluded()
    {
        return $this->taxIncluded;
    }

    /**
     * @param float $taxIncluded
     */
    public function setTaxIncluded($taxIncluded)
    {
        $this->taxIncluded = (float) $taxIncluded;
    }

    /**
     *
     * @return integer
     */
    public function getProductQuantity()
    {
        return $this->productQuantity;
    }

    /**
     *
     * @param integer $productQuantity
     */
    public function setProductQuantity($productQuantity)
    {
        $this->productQuantity = (int) $productQuantity;
    }

    /**
     *
     * @return float
     */
    public function getDiscountPrice()
    {
        return $this->discountPrice;
    }

    /**
     *
     * @param float $discountPrice
     */
    public function setDiscountPrice($discountPrice)
    {
        $this->discountPrice = (float) $discountPrice;
    }

    /**
     *
     * @return float
     */
    public function getOrderPrice()
    {
        return $this->orderPrice;
    }

    /**
     *
     * @param float $orderPrice
     */
    public function setOrderPrice($orderPrice)
    {
        $this->orderPrice = (float) $orderPrice;
    }
}
