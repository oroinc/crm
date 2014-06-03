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
    protected $reporterUser;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $reporterContact;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $reporterCustomer;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $relatedOrder;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $relatedCart;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $relatedLead;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $relatedOpportunity;

    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $origin;

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
        $this->id                 = $case->getId();
        $this->subject            = $case->getSubject();
        $this->description        = $case->getDescription();
        $this->owner              = $this->getEntityId($case->getOwner());
        $this->reporterUser       = $this->getEntityId($case->getReporterUser());
        $this->reporterContact    = $this->getEntityId($case->getReporterContact());
        $this->reporterCustomer   = $this->getEntityId($case->getReporterCustomer());
        $this->relatedCart        = $this->getEntityId($case->getRelatedCart());
        $this->relatedLead        = $this->getEntityId($case->getRelatedLead());
        $this->relatedOrder       = $this->getEntityId($case->getRelatedOrder());
        $this->relatedOpportunity = $this->getEntityId($case->getRelatedOpportunity());
        $this->origin             = $case->getOrigin() ? $case->getOrigin()->getCode() : null;
        $this->workflowStep       = $this->getEntityId($case->getWorkflowStep());
        $this->workflowItem       = $this->getEntityId($case->getWorkflowItem());
        $this->createdAt          = $case->getCreatedAt();
        $this->updatedAt          = $case->getUpdatedAt();
        $this->reportedAt         = $case->getReportedAt();
        $this->closedAt           = $case->getClosedAt();
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
