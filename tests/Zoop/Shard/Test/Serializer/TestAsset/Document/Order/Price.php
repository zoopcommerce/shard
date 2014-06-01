<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document\Order;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\EmbeddedDocument
 */
class Price
{
    /**
     * Wholesale price per unit
     * 
     * @ODM\Float
     */
    protected $wholesale;

    /**
     * List price per unit
     * 
     * @ODM\Float
     */
    protected $list;

    /**
     * List x Quantity
     * 
     * @ODM\Float
     */
    protected $subTotal;

    /**
     * Discount per unit
     * 
     * @ODM\Float
     */
    protected $discount;

    /**
     * (List x Quantity) - (Discount x Quantity)
     * 
     * @ODM\Float
     */
    protected $total;

    /**
     * Tax included per unit
     * 
     * @ODM\Float
     */
    protected $taxIncluded;

    /**
     * Shipping cost per unit (not included in total)
     * 
     * @ODM\Float
     */
    protected $shipping;

    /**
     * Wholesale price per unit
     * 
     * @return float
     */
    public function getWholesale()
    {
        return $this->wholesale;
    }

    /**
     *
     * @param float $wholesale
     */
    public function setWholesale($wholesale)
    {
        $this->wholesale = (float) $wholesale;
    }

    /**
     * List price per unit
     * @return float
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     *
     * @param float $list
     */
    public function setList($list)
    {
        $this->list = (float) $list;
    }

    /**
     * List x Quantity
     * 
     * @return float
     */
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     *
     * @param float $subTotal
     */
    public function setSubTotal($subTotal)
    {
        $this->subTotal = (float) $subTotal;
    }

    /**
     * Discount per unit
     * 
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     *
     * @param float $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = (float) $discount;
    }

    /**
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * (List x Quantity) - (Discount x Quantity)
     * 
     * @param float $total
     */
    public function setTotal($total)
    {
        $this->total = (float) $total;
    }

    /**
     * Tax included per unit
     * 
     * @return float
     */
    public function getTaxIncluded()
    {
        return $this->taxIncluded;
    }

    /**
     *
     * @param float $taxIncluded
     */
    public function setTaxIncluded($taxIncluded)
    {
        $this->taxIncluded = (float) $taxIncluded;
    }

    /**
     * Shipping cost per unit (not included in total)
     * 
     * @return float
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     *
     * @param float $shipping
     */
    public function setShipping($shipping)
    {
        $this->shipping = (float) $shipping;
    }
}
