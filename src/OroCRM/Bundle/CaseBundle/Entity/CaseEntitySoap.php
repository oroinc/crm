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
    protected $reporter;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $item;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $origins;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $workflowStep;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $workflowItem;

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
        $this->id           = $case->getId();
        $this->subject      = $case->getSubject();
        $this->description  = $case->getDescription();
        $this->owner        = $this->getEntityId($case->getOwner());
        $this->reporter     = $this->getEntityId($case->getReporter());
        $this->item         = $this->getEntityId($case->getItem());
        $this->origin       = $case->getOrigin() ? $case->getOrigin()->getCode() : null;
        $this->workflowStep = $this->getEntityId($case->getWorkflowStep());
        $this->workflowItem = $this->getEntityId($case->getWorkflowItem());
        $this->createdAt    = $case->getCreatedAt();
        $this->updatedAt    = $case->getUpdatedAt();
        $this->reportedAt   = $case->getReportedAt();
        $this->closedAt     = $case->getClosedAt();
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
