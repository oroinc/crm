<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestParameters implements ParametersInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        return $this->getRequest()->get($name, $default);
    }
    
    /**
     * @return Request
     */
    protected function getRequest()
    {
        // We should not cache request as it is scopable
        return $this->container->get('request');
    }
}
