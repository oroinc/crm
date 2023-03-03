<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractEmail;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Entity holds one email of Business Customer. Will be used in collection of emails and can be marked as primary.
 *
 * @ORM\Entity
 * @ORM\Table("orocrm_sales_b2bcustomer_email", indexes={
 *      @ORM\Index(name="primary_email_idx", columns={"email", "is_primary"})
 * })
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-envelope"
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
class B2bCustomerEmail extends AbstractEmail implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="B2bCustomer", inversedBy="emails")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;

    /**
     * @param B2bCustomer $owner
     */
    public function setOwner(B2bCustomer $owner = null)
    {
        $this->owner = $owner;
        $this->owner->addEmail($this);
    }

    /**
     * @return B2bCustomer
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
