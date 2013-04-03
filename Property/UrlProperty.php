<?php

namespace Oro\Bundle\GridBundle\Property;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

class UrlProperty extends AbstractProperty
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var router
     */
    protected $router;

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var array
     */
    protected $placeholders;

    /**
     * @var bool
     */
    protected $isAbsolute;

    /**
     * @param string $name
     * @param Router $router
     * @param string $routeName
     * @param array $placeholders
     * @param bool $isAbsolute
     */
    public function __construct($name, Router $router, $routeName, array $placeholders = array(), $isAbsolute = false)
    {
        $this->name = $name;
        $this->router = $router;
        $this->routeName = $routeName;
        $this->placeholders = $placeholders;
        $this->isAbsolute = $isAbsolute;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($data)
    {
        return $this->router->generate($this->routeName, $this->getParameters($data), $this->isAbsolute);
    }

    protected function getParameters($data)
    {
        $result = array();
        foreach ($this->placeholders as $name => $dataKey) {
            if (is_numeric($name)) {
                $name = $dataKey;
            }
            $result[$name] = $this->getDataValue($data, $dataKey);
        }
        return $result;
    }
}
