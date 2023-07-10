<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\FormBundle\Entity\PrimaryItem;

/**
 * Lead address entity
 * @ORM\Table("orocrm_sales_lead_address")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *       defaultValues={
 *          "entity"={
 *              "icon"="fa-map-marker"
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
class LeadAddress extends AbstractAddress implements PrimaryItem, ExtendEntityInterface
{
    use ExtendEntityTrait;

    /**
     * @var Lead|null
     *
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
        $this->primary = false;
    }

    /**
     * Set lead as owner.
     */
    public function setOwner(Lead $owner = null)
    {
        if (null === $owner && null !== $this->owner) {
            $this->owner->removeAddress($this);
        }
        $this->owner = $owner;
        if (null !== $this->owner) {
            $this->owner->addAddress($this);
        }
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
