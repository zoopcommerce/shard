<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\EmbeddedDocument
 */
class Administrator extends AbstractUser implements UserInterface
{
    
}
