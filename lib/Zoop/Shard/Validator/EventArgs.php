<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Validator;

use Doctrine\Common\EventArgs as BaseEventArgs;
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
     * @var array
     */
    protected $result;

    /**
     *
     * @param object $document
     * @param array  $messages
     */
    public function __construct($document, Result $result)
    {
        $this->document = $document;
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

    public function getResult()
    {
        return $this->result;
    }
}
