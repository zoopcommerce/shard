<?php

namespace Zoop\Shard\Test\Annotation\TestAsset\Document;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\Serializer\ClassName(false)
 * @Shard\Serializer\Discriminator(false)
 * @Shard\Validator\Chain({
 *     @Shard\Validator(class = "ParentValidator", value = false),
 *     @Shard\Validator(class = "ChildBValidator")
 * })
 */
class ChildB extends ParentClass
{
    /**
     * @Shard\Serializer\Ignore(false)
     */
    protected $name;
}
