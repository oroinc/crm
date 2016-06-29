<?php

namespace OroCRM\Bundle\SalesBundle\Model;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadStatus;

class ChangeLeadStatus
{
    const STATUS_QUALIFY    = 'qualified';
    const STATUS_DISQUALIFY = 'canceled';
    const LEAD_VIEW_ROUTE   = 'orocrm_sales_lead_view';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var DoctrineHelper
     */
    protected $manager;

    /**
     * @param Request $request
     * @param Session $session
     * @param Router $router
     * @param ObjectManager $manager
     */
    public function __construct(
        Request $request,
        Session $session,
        Router $router,
        ObjectManager $manager
    ) {
        $this->request = $request;
        $this->session = $session;
        $this->router = $router;
        $this->manager = $manager;
    }

    /**
     * @param Lead $lead
     *
     * @return RedirectResponse
     */
    public function disqualify(Lead $lead)
    {
        $this->changeStatus($lead, self::STATUS_DISQUALIFY);

        return new RedirectResponse($this->router->generate(self::LEAD_VIEW_ROUTE, ['id' => $lead->getId()]));
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
            $this->session->getFlashBag()->add('success', 'Saved');
        } catch (\Exception $e) {
            $this->session->getFlashBag()->add('error', 'Not saved');
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
