<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
abstract class AbstractAccessControlSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    protected $accessController;

    protected $documentManager;

    protected function getAccessController()
    {
        if (! isset($this->accessController)) {
            if ($this->serviceLocator->has('accessController')) {
                $this->accessController = $this->serviceLocator->get('accessController');
            }
        }
        return $this->accessController;
    }

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
