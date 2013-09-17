<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

trait ModelManagerAwareTrait
{

    protected $modelManager = null;

    public function setModelManager(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;

        return $this;
    }

    public function getModelManager()
    {
        return $this->modelManager;
    }
}
