<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

use Doctrine\Common\EventArgs as BaseEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Arguments for access control events
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
    protected $action;

    /**
     *
     * @param object $document
     * @param \Doctrine\ODM\MongoDB\DocumentManager $documentManager
     * @param array $messages
     */
    public function __construct(
        $document,
        DocumentManager $documentManager,
        $action
    ) {
        $this->document = $document;
        $this->documentManager = $documentManager;
        $this->action = (string) $action;
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

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = (string) $action;
    }
}
