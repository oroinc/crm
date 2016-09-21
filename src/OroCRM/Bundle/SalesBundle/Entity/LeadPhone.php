<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use OroCRM\Bundle\SalesBundle\Model\ExtendLeadPhone;

/**
 * @ORM\Entity
 * @ORM\Table("orocrm_sales_lead_phone", indexes={
 *      @ORM\Index(name="primary_phone_idx", columns={"phone", "is_primary"}),
 *      @ORM\Index(name="phone_idx", columns={"phone"})
 * })
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-phone"
 *          },
 *          "note"={
 *              "immutable"=true
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
class LeadPhone extends ExtendLeadPhone
{
    /**
     * @ORM\ManyToOne(targetEntity="Lead", inversedBy="phones", cascade={"persist"})
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
