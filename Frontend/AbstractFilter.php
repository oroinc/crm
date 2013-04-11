<?php

namespace Oro\Bundle\FilterBundle\Frontend;

abstract class AbstractFilter implements FilterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $options;

    /**
     * Set name
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        if (null === $this->options) {
            $this->options = $this->getDefaultOptions();
        }
        return $this->options;
    }

    /**
     * Gets default options
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * Set options
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);
    }

    /**
     * Add options
     *
     * @param array $options
     */
    public function addOptions(array $options)
    {
        $this->options = array_merge($this->getOptions(), $options);
    }

    /**
     * Checks filter value is valid
     *
     * @param string $data
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isValid($data)
    {
        return true;
    }

    /**
     * Parses filter value
     *
     * @param mixed $data
     * @return mixed
     */
    public function parseValue($data)
    {
        return $data;
    }
}
