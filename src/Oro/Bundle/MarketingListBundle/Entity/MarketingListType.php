<?php

namespace Oro\Bundle\MarketingListBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_marketing_list_type")
 * @Config()
 */
class MarketingListType
{
    const TYPE_DYNAMIC = 'dynamic';
    const TYPE_STATIC  = 'static';
    const TYPE_MANUAL  = 'manual';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32)
     * @ORM\Id
     */
    protected $name;

    /**
     * @var string
     *
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
     * Get type name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label.
     *
     * @param string $label
     *
     * @return MarketingListType
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->label;
    }
}
