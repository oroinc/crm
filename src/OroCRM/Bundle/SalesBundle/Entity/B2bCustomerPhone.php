<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use OroCRM\Bundle\SalesBundle\Model\ExtendB2bCustomerPhone;

/**
 * @ORM\Entity
 * @ORM\Table("orocrm_b2bcustomer_phone", indexes={
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
class B2bCustomerPhone extends ExtendB2bCustomerPhone
{
    /**
     * @ORM\ManyToOne(targetEntity="B2bCustomer", inversedBy="phones")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;
    /**
     * Set B2b customer as owner.
     *
     * @param B2bCustomer $owner
     */
    public function setOwner(B2bCustomer $owner = null)
    {
        $this->owner = $owner;
    }
    /**
     * Get owner B2bCustomer.
     *
     * @return B2bCustomer
     */
    public function getOwner()
    {
        return $this->owner;
    }
}