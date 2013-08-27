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

    public function setClassResults(array $classResults)
    {
        $this->classResults = $classResults;
    }

    public function addClassResult(Result $result)
    {
        $this->classResults[] = $result;
    }

    public function getFieldResults()
    {
        return $this->fieldResults;
    }

    public function setFieldResults(array $fieldResults)
    {
        $this->fieldResults = $fieldResults;
    }

    public function addFieldResult($field, Result $result)
    {
        $this->fieldResults[$field] = $result;
    }
}
