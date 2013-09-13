<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Validator;

use Zoop\Mystique\Result;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DocumentValidatorResult extends Result
{

    protected $classResults = [];

    protected $fieldResults = [];

    public function getClassResults()
    {
        return $this->classResults;
    }

    public function addClassResult(Result $result)
    {
        $this->classResults[] = $result;
        foreach ($result->getMessages() as $message) {
            $this->addMessage('document: ' . $message);
        }
        if (! $result->getValue()) {
            $this->setValue(false);
        }
    }

    public function getFieldResults()
    {
        return $this->fieldResults;
    }

    public function addFieldResult($field, Result $result)
    {
        $this->fieldResults[$field] = $result;
        foreach ($result->getMessages() as $message) {
            $this->addMessage($field . ': ' . $message);
        }
        if (! $result->getValue()) {
            $this->setValue(false);
        }
    }
}
