<?php

namespace OroCRM\Bundle\CaseBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\SoapBundle\Entity\SoapEntityInterface;

/**
 * @Soap\Alias("OroCRM.Bundle.CaseBundle.Entity.CaseEntity")
 */
class CaseSoap extends CaseEntity implements SoapEntityInterface
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
    protected $reportedOn;

    /**
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $closedOn;

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
        $this->origins      = $this->getEntityIds($case->getOrigins());
        $this->workflowStep = $this->getEntityId($case->getWorkflowStep());
        $this->workflowItem = $this->getEntityId($case->getWorkflowItem());
        $this->createdAt    = $case->getCreatedAt();
        $this->updatedAt    = $case->getUpdatedAt();
        $this->reportedOn   = $case->getReportedOn();
        $this->closedOn     = $case->getClosedOn();
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

    /**
     * @param Collection $collection
     *
     * @return array
     */
    protected function getEntityIds($collection)
    {
        $ids = [];

        if ($collection->count()) {
            foreach ($collection->getValues() as $item) {
                $ids[] = $item->getId();
            }
        }

        return $ids;
    }
}
