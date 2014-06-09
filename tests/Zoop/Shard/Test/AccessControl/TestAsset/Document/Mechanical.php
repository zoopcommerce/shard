<?php

namespace Zoop\Shard\Test\AccessControl\TestAsset\Document;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\EmbeddedDocument
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="artist", allow="*"),
 *     @Shard\Permission\Basic(roles="guest", deny="*")
 * })
 */
class Mechanical
{
    /**
     * @ODM\String
     */
    protected $name;

    /**
     * @ODM\Float
     */
    protected $amount;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = (float) $amount;
    }

    public function __construct($name, $amount)
    {
        $this->setName($name);
        $this->setAmount($amount);
    }
}
