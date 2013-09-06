<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\Common\EventArgs as BaseEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class CoreEventArgs extends BaseEventArgs
{
    const CREATE = 'create';
    const DELETE = 'delete';
    const UPDATE = 'update';

    protected $document;

    protected $action;

    protected $shortCircut = false;

    public function __construct($document, $action) {
        $this->document = $document;
        $this->action = $action;
    }

    public function getDocument() {
        return $this->document;
    }

    public function getAction() {
        return $this->action;
    }

    public function getShortCircut() {
        return $this->shortCircut;
    }

    public function setShortCircut($shortCircut) {
        $this->shortCircut = $shortCircut;
    }
}
