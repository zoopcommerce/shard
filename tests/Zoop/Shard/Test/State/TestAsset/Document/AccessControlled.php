<?php

namespace Zoop\Shard\Test\State\TestAsset\Document;

use Zoop\Shard\State\DataModel\StateTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\State(
 *         roles="*",
 *         states="published", allow="read"
 *     ),
 *     @Shard\Permission\State(
 *         roles="writer",
 *         states="draft",
 *         allow={"create", "update", "read"}
 *     ),
 *     @Shard\Permission\Transition(
 *         roles="writer",
 *         allow="draft->review"
 *     ),
 *     @Shard\Permission\State(
 *         roles="reviewer",
 *         states="review",
 *         allow={"update", "read"}
 *     ),
 *     @Shard\Permission\Transition(
 *         roles="reviewer",
 *         allow={"review->draft", "review->published"},
 *         deny="draft->review"
 *     ),
 *     @Shard\Permission\Basic(
 *         roles="admin",
 *         allow="*"
 *     ),
 *     @Shard\Permission\State(
 *         roles="glob",
 *         states={"d*", "r*"},
 *         allow={"read", "create"}
 *     ),
 *     @Shard\Permission\Transition(
 *         roles="glob",
 *         allow="draft->*"
 *     )
 * })
 */
class AccessControlled
{
    use StateTrait;

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     */
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
