<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard;

use Doctrine\ODM\MongoDB\DocumentManager;

trait DocumentManagerAwareTrait
{

    protected $documentManager = null;

    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;

        return $this;
    }

    public function getDocumentManager()
    {
        return $this->documentManager;
    }
}
