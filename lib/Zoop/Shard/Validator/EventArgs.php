<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Validator;

use Doctrine\Common\EventArgs as BaseEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\Mystique\Result;

/**
 * Arguments for invalid events
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class EventArgs extends BaseEventArgs
{
    /**
     * The document with the changed field
     *
     * @var object
     */
    protected $document;

    /**
     *
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $documentManager;

    /**
     *
     * @var array
     */
    protected $result;

    /**
     *
     * @param object $document
     * @param \Doctrine\ODM\MongoDB\DocumentManager $documentManager
     * @param array $messages
     */
    public function __construct(
        $document,
        DocumentManager $documentManager,
        Result $result
    ) {
        $this->document = $document;
        $this->documentManager = $documentManager;
        $this->result = $result;
    }

    /**
     *
     * @return object
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     *
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult(Result $result)
    {
        $this->result = $result;
    }
}
