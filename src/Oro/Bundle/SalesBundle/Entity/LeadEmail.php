<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractEmail;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Lead email entity
 * @ORM\Entity
 * @ORM\Table("orocrm_sales_lead_email", indexes={
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
class LeadEmail extends AbstractEmail implements ExtendEntityInterface, EmailInterface
{
    use ExtendEntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Lead", inversedBy="emails")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;

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
     * {@inheritDoc}
     */
    public function getEmailField()
    {
        return 'email';
    }

    /**
     * {@inheritDoc}
     */
    public function getEmailOwner()
    {
        return $this->getOwner();
    }
}
