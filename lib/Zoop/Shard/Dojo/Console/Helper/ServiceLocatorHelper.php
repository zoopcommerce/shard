<?php

namespace Zoop\Shard\Dojo\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceLocatorHelper extends Helper
{
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    public function getName()
    {
        return 'serviceLocator';
    }
}
