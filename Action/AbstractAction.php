<?php

namespace Oro\Bundle\GridBundle\Action;

use Symfony\Component\Routing\RouterInterface;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var bool
     */
    protected $isProcessed = false;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Filter name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Action type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Action options (route, ACL resource etc.)
     *
     * @return array
     */
    public function getOptions()
    {
        if (!$this->isProcessed) {
            $this->processRouteOptions();
        }

        return $this->options;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param string $optionName
     * @throws \LogicException
     */
    protected function assertOption($optionName)
    {
        if (!isset($this->options[$optionName])) {
            throw new \LogicException(
                'There is no option "' . $optionName . '" for action "' . $this->name . '".'
            );
        }
    }

    /**
     * Process router options ("route", "parameters", "placeholders")
     *
     * @throws \LogicException
     */
    protected function processRouteOptions()
    {
        $this->assertOption('route');

        $routeName = $this->options['route'];

        $route = $this->router->getRouteCollection()->get($routeName);
        if (!$route) {
            throw new \LogicException('There is no route with name "' . $routeName . '".');
        }

        // process parameters
        if (isset($this->options['parameters'])) {
            $parameters = $this->options['parameters'];
            unset($this->options['parameters']);
        } else {
            $parameters = array();
        }

        // process placeholders
        if (!isset($this->options['placeholders'])) {
            $this->options['placeholders'] = array();
        }
        $placeholders = $this->options['placeholders'];

        $routePattern = $route->getPattern();

        // process placeholders in route
        preg_match_all('/{(.*?)}/', $routePattern, $routePlaceholders, PREG_SET_ORDER);
        $replaceFrom = array();
        $replaceTo   = array();
        foreach ($routePlaceholders as $placeholder) {
            $placeholderPattern = $placeholder[0];
            $placeholderName    = $placeholder[1];

            // if need to be replaced
            if (!isset($placeholders[$placeholderPattern])) {
                if (isset($parameters[$placeholderPattern])) {
                    $replaceFrom[] = $placeholderPattern;
                    $replaceTo[]   = $parameters[$placeholderPattern];
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

        $this->options['route'] = str_replace($replaceFrom, $replaceTo, $routePattern);
    }
}
