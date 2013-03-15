<?php

namespace Oro\Bundle\GridBundle\Action;

use Symfony\Component\Routing\RouterInterface;

class ActionUrlGenerator implements ActionUrlGeneratorInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $routeName
     * @param array $parameters
     * @param array $placeholders
     * @return string|void
     * @throws \LogicException
     */
    public function generate($routeName, array $parameters = array(), array $placeholders = array())
    {
        $route = $this->router->getRouteCollection()->get($routeName);
        if (!$route) {
            throw new \LogicException('There is no route with name "' . $routeName . '".');
        }

        $routePattern = $route->getPattern();

        // process placeholders in route
        preg_match_all('/{(.*?)}/', $routePattern, $routePlaceholders, PREG_SET_ORDER);
        $replaceFrom = array();
        $replaceTo   = array();
        foreach ($routePlaceholders as $placeholder) {
            $placeholderPattern = $placeholder[0];
            $placeholderName    = $placeholder[1];

            // if need to be replaced
            if (!isset($placeholders[$placeholderName])) {
                if (isset($parameters[$placeholderName])) {
                    $replaceFrom[] = $placeholderPattern;
                    $replaceTo[]   = $parameters[$placeholderName];
                } else {
                    $defaultValue = $route->getDefault($placeholderName);
                    if ($defaultValue === null) {
                        throw new \LogicException(
                            'There is no placeholder with name "' . $placeholderPattern . '"'
                            . ' for route "' . $routeName . '".'
                        );
                    }
                    $replaceFrom[] = $placeholderPattern;
                    $replaceTo[]   = $defaultValue;
                }
            }
        }

        return $this->router->getContext()->getBaseUrl() . str_replace($replaceFrom, $replaceTo, $routePattern);
    }
}
