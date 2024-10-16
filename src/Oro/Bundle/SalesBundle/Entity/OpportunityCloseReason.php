<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;

/**
* Entity that represents Opportunity Close Reason
*
*/
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_sales_opport_close_rsn')]
#[Config(defaultValues: ['grouping' => ['groups' => ['dictionary']]])]
class OpportunityCloseReason
{
    #[ORM\Column(name: 'name', type: Types::STRING, length: 32)]
    #[ORM\Id]
    #[ConfigField(defaultValues: ['importexport' => ['identity' => true]])]
    protected ?string $name = null;

    #[ORM\Column(name: 'label', type: Types::STRING, length: 255, unique: true)]
    protected ?string $label = null;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get type name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set address type label
     *
     * @param  string            $label
     * @return OpportunityCloseReason
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get address type label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    #[\Override]
    public function __toString()
    {
        return (string) $this->label;
    }
}
