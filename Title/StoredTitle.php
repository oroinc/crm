<?php

namespace Oro\Bundle\NavigationBundle\Title;
use JMS\Serializer\Annotation\Type;

/**
 * Class StoredTitle
 * Used for json desirialization
 * @package Oro\Bundle\NavigationBundle\Title
 */
class StoredTitle
{
    /**
     * @Type("string")
     * @var string
     */
    private $template;

    /**
     * @Type("array")
     * @var array
     */
    private $params = array();

    /**
     * Setter for template
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Getter for template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Setter for params
     *
     * @param array $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Getter for params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
