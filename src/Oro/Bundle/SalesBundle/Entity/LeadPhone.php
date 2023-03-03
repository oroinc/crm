<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractPhone;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Lead phone entity
 * @ORM\Entity
 * @ORM\Table("orocrm_sales_lead_phone", indexes={
 *      @ORM\Index(name="primary_phone_idx", columns={"phone", "is_primary"}),
 *      @ORM\Index(name="phone_idx", columns={"phone"})
 * })
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-phone"
 *          },
 *          "comment"={
 *              "immutable"=true
 *          },
 *          "activity"={
 *              "immutable"=true
 *          },
 *          "attachment"={
 *              "immutable"=true
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 */
class LeadPhone extends AbstractPhone implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Lead", inversedBy="phones")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;

    /**
     * Set Lead as owner.
     *
     * @param Lead $owner
     */
    public function setOwner(Lead $owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner Lead.
     *
     * @return Lead
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
