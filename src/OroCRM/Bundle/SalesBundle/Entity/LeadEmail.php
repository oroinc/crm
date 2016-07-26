<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use OroCRM\Bundle\SalesBundle\Model\ExtendLeadEmail;

/**
 * @ORM\Entity
 * @ORM\Table("orocrm_sales_lead_email", indexes={
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
class LeadEmail extends ExtendLeadEmail implements EmailInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="Lead", inversedBy="emails")
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
}
