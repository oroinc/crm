<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use OroCRM\Bundle\SalesBundle\Model\ExtendB2bCustomerEmail;

/**
 * @ORM\Entity
 * @ORM\Table("orocrm_sales_b2bcustomer_email", indexes={
 *      @ORM\Index(name="primary_email_idx", columns={"email", "is_primary"})
 * })
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-envelope"
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
class B2bCustomerEmail extends ExtendB2bCustomerEmail implements EmailInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="B2bCustomer", inversedBy="emails")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;

    /**
     * {@inheritdoc}
     */
    public function getEmailField()
    {
        return 'email';
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailOwner()
    {
        return $this->getOwner();
    }

    /**
     * Set contact as owner.
     *
     * @param B2bCustomer $owner
     */
    public function setOwner(B2bCustomer $owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner contact.
     *
     * @return B2bCustomer
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
