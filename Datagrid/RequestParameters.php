<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestParameters implements ParametersInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    protected $rootParameterName;

    /**
     * @var array
     */
    static protected $usedParameterTypes = array(
        self::FILTER_PARAMETERS,
        self::PAGER_PARAMETERS,
        self::SORT_PARAMETERS
    );

    /**
     * @param ContainerInterface $container
     * @param string $rootParameterName
     */
    public function __construct(ContainerInterface $container, $rootParameterName)
    {
        $this->container = $container;
        $this->rootParameterName = $rootParameterName;
    }

    /**
     * Get parameter value from parameters container
     *
     * @param string $type
     * @param mixed $default
     * @return mixed
     */
    public function get($type, $default = array())
    {
        $rootParameter = $this->getRootParameterValue();
        return isset($rootParameter[$type]) ? $rootParameter[$type] : $default;
    }

    /**
     * @return array
     */
    protected function getRootParameterValue()
    {
        return $this->getRequest()->get($this->rootParameterName, array());
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->container->get('request');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = array($this->rootParameterName => array());

        foreach (self::$usedParameterTypes as $type) {
            $value = $this->get($type, array());
            if (!empty($value)) {
                $result[$this->rootParameterName][$type] = $value;
            }
        }

        return $result;
    }
}
