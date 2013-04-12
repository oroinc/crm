<?php

namespace Oro\Bundle\GridBundle\Route;

use Symfony\Component\Routing\RouterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use \Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

class DefaultRouteGenerator implements RouteGeneratorInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var array
     */
    private $routeParameters;

    /**
     * @param RouterInterface $router
     * @param string $routeName
     */
    public function __construct(RouterInterface $router, $routeName)
    {
        $this->router = $router;
        $this->routeName = $routeName;
        $this->routeParameters = array();
    }

    /**
     * {@inheritdoc}
     */
    public function generateUrl(ParametersInterface $parameters = null, array $extendParameters = array())
    {
        if ($parameters) {
            $routeParameters = array_merge_recursive($parameters->toArray(), $extendParameters);
        } else {
            $routeParameters = $extendParameters;
        }
        $routeParameters = array_merge($routeParameters, $this->routeParameters);

        return $this->generate($this->routeName, $routeParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function generateSortUrl(ParametersInterface $parameters, FieldDescriptionInterface $field, $direction)
    {
        $routeParameters = $parameters->toArray();
        $parameterKeys = array_keys($routeParameters);
        $rootParameter = array_shift($parameterKeys);
        $routeParameters[$rootParameter][ParametersInterface::SORT_PARAMETERS] = array(
            $field->getName() => $direction
        );
        $routeParameters = array_merge($routeParameters, $this->routeParameters);

        return $this->generate($this->routeName, $routeParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function generatePagerUrl(ParametersInterface $parameters, $page, $perPage = null)
    {
        $routeParameters = $parameters->toArray();
        $parameterKeys = array_keys($routeParameters);
        $rootParameter = array_shift($parameterKeys);
        $routeParameters[$rootParameter][ParametersInterface::PAGER_PARAMETERS]['_page'] = $page;
        if (null !== $perPage) {
            $routeParameters[$rootParameter][ParametersInterface::PAGER_PARAMETERS]['_per_page'] = $perPage;
        }
        $routeParameters = array_merge($routeParameters, $this->routeParameters);

        return $this->generate($this->routeName, $routeParameters);
    }

    /**
     * Set array with route parameters
     * @param array $parameters
     */
    public function setRouteParameters(array $parameters)
    {
        $this->routeParameters = $parameters;
    }

    /**
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    protected function generate($name, $parameters = array(), $absolute = false)
    {
        return $this->router->generate($name, $parameters, $absolute);
    }
}
