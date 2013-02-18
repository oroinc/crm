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
     * @var array
     */
    protected $parameters;

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
    public function getParameters($datagridId = null)
    {
        if (null === $this->parameters) {
            /** @var $request Request */
            $request = $this->container->get('request');
            $this->initParameters($request, $datagridId);
        }

        if ($datagridId) {
            return isset($this->parameters[$datagridId]) ? $this->parameters[$datagridId] : null;
        }

        return !empty($this->parameters) ? array_shift($this->parameters) : null;
    }

    /**
     * @param Request $request
     * @param string $datagridId
     */
    protected function initParameters(Request $request, $datagridId)
    {
        $this->parameters[$datagridId] = array();

        $this->initFilterParameters($request, $datagridId);
        $this->initSorterParameters($request, $datagridId);
        $this->initPagerParameters($request, $datagridId);
    }

    /**
     * @param Request $request
     * @param string $datagridId
     */
    protected function initFilterParameters(Request $request, $datagridId)
    {
        // TODO
    }

    /**
     * @param Request $request
     * @param string $datagridId
     */
    protected function initSorterParameters(Request $request, $datagridId)
    {
        // TODO
    }

    /**
     * @param Request $request
     * @param string $datagridId
     */
    protected function initPagerParameters(Request $request, $datagridId)
    {
        // TODO
    }
}
