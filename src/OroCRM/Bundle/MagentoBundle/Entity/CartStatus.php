<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_cart_status")
 * @Config()
 */
class CartStatus
{
    /**
     * @ORM\Column(name="name", type="string", length=32)
     * @ORM\Id
     */
    protected $name;

    /**
     * @ORM\Column(name="label", type="string", length=255, unique=true)
     */
    protected $label;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $label
     * @return CartStatus
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->label;
    }
}
