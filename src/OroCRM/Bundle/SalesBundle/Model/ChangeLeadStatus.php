<?php

namespace OroCRM\Bundle\SalesBundle\Model;

use Symfony\Component\HttpFoundation\Session\Session;

use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadStatus;

class ChangeLeadStatus
{
    const STATUS_QUALIFY    = 'qualified';
    const STATUS_DISQUALIFY = 'canceled';

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param Session $session
     * @param ObjectManager $manager
     */
    public function __construct(
        Session $session,
        ObjectManager $manager
    ) {
        $this->session = $session;
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
            $status = $this->getStatusEntityByName($statusCode);
            $this->save($lead->setStatus($status));
        } catch (\Exception $e) {
            $this->session->getFlashBag()->add('error', $e->getMessage());
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

    /**
     * @param string $leadStatusName
     *
     * @return LeadStatus
     */
    protected function getStatusEntityByName($leadStatusName)
    {
        $repository = $this->manager->getRepository('OroCRMSalesBundle:LeadStatus');

        $result = $repository->createQueryBuilder('ls')
                             ->where('ls.name = :lead_status_name')
                             ->setParameter(':lead_status_name', $leadStatusName)
                             ->getQuery()
                             ->getSingleResult();

        return $result;
    }
}
