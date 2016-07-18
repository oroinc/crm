<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\FormBundle\Entity\PrimaryItem;
use OroCRM\Bundle\SalesBundle\Model\ExtendLeadAddress;

/**
 * @ORM\Table("orocrm_sales_lead_address")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *       defaultValues={
 *          "entity"={
 *              "icon"="icon-map-marker"
 *          },
 *          "note"={
 *              "immutable"=true
 *          },
 *          "activity"={
 *              "immutable"=true
 *          },
 *          "attachment"={
 *              "immutable"=true
 *          }
 *      }
 * )
 * @ORM\Entity
 */
class LeadAddress extends ExtendLeadAddress implements PrimaryItem
{
    /**
     * @ORM\ManyToOne(targetEntity="Lead", inversedBy="addresses", cascade={"persist"})
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $owner;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=true)
     * @Soap\ComplexType("boolean", nillable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $primary;

    public function __construct()
    {
        parent::__construct();
        $this->primary = false;
    }

    /**
     * Set lead as owner.
     *
     * @param Lead $owner
     */
    public function setOwner(Lead $owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner lead.
     *
     * @return Lead
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param bool $primary
     * @return LeadAddress
     */
    public function setPrimary($primary)
    {
        $this->primary = (bool)$primary;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimary()
    {
        return (bool)$this->primary;
    }
}
