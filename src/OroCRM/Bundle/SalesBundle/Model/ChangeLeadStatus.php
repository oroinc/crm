<?php

namespace OroCRM\Bundle\SalesBundle\Model;

use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadStatus;

class ChangeLeadStatus
{
    const STATUS_QUALIFY    = 'qualified';
    const STATUS_DISQUALIFY = 'canceled';

    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @param EntityManager $manager
     */
    public function __construct(
        EntityManager $manager
    ) {
        $this->manager = $manager;
    }

    /**
     * @param Lead $lead
     *
     * @return bool
     */
    public function disqualify(Lead $lead)
    {
        return $this->changeStatus($lead, self::STATUS_DISQUALIFY);
    }

    /**
     * @param Lead $lead
     *
     * @return bool
     */
    public function qualify(Lead $lead)
    {
        return $this->changeStatus($lead, self::STATUS_QUALIFY);
    }

    /**
     * @param Lead   $lead
     * @param string $statusCode
     *
     * @return bool
     */
    protected function changeStatus($lead, $statusCode)
    {
        try {
            $status = $this->manager->getReference('OroCRMSalesBundle:LeadStatus', $statusCode);
            $lead->setStatus($status);
            $this->save($lead);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param Lead $entity
     */
    protected function save(Lead $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
