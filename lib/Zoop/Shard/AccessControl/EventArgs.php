<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

use Doctrine\Common\EventArgs as BaseEventArgs;

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
     * @var array
     */
    protected $action;

    /**
     *
     * @param object $document
     * @param array $messages
     */
    public function __construct(
        $document,
        $action
    ) {
        $this->document = $document;
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

    public function getAction()
    {
        return $this->action;
    }
}
