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
     * @var Request
     */
    protected $request;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = $this->container->get('request');
        }

        return $this->request;
    }

    /**
     * Get parameter name from parameters container
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->request->get($name);
    }
}
