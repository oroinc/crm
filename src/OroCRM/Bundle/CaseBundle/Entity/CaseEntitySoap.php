<?php

namespace OroCRM\Bundle\CaseBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SoapBundle\Entity\SoapEntityInterface;

/**
 * @Soap\Alias("OroCRM.Bundle.CaseBundle.Entity.CaseEntity")
 */
class CaseEntitySoap extends CaseEntity implements SoapEntityInterface
{
    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $subject;

    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $description;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $owner;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $relatedContact;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $relatedAccount;

    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $source;

    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $status;

    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $priority;

    /**
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $createdAt;

    /**
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $updatedAt;

    /**
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $reportedAt;

    /**
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $closedAt;

    /**
     * @param CaseEntity $case
     */
    public function soapInit($case)
    {
        $this->id             = $case->getId();
        $this->subject        = $case->getSubject();
        $this->description    = $case->getDescription();
        $this->owner          = $this->getEntityId($case->getOwner());
        $this->assignedTo     = $this->getEntityId($case->getAssignedTo());
        $this->relatedContact = $this->getEntityId($case->getRelatedContact());
        $this->relatedAccount = $this->getEntityId($case->getRelatedAccount());
        $this->source         = $case->getSource() ? $case->getSource()->getName() : null;
        $this->status         = $case->getStatus() ? $case->getStatus()->getName() : null;
        $this->priority       = $case->getPriority() ? $case->getPriority()->getName() : null;
        $this->createdAt      = $case->getCreatedAt();
        $this->updatedAt      = $case->getUpdatedAt();
        $this->reportedAt     = $case->getReportedAt();
        $this->closedAt       = $case->getClosedAt();
    }

    /**
     * @param object $entity
     *
     * @return integer|null
     */
    protected function getEntityId($entity)
    {
        if ($entity) {
            return $entity->getId();
        }

        return null;
    }
}
