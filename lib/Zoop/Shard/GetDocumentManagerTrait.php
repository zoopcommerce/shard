<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait GetDocumentManagerTrait
{

    protected $documentManager;

    protected function getDocumentManager()
    {
        if (! isset($this->documentManager)) {
            $this->documentManager = $this->serviceLocator->get('manifest')->getDocumentManager();
            if (is_string($this->documentManager)){
                $this->documentManager = $this->serviceLocator->get($this->documentManager);
            }
        }
        return $this->documentManager;
    }
}
