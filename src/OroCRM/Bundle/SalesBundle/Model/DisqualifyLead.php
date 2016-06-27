<?php

namespace OroCRM\Bundle\SalesBundle\Model;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadStatus;

class DisqualifyLead
{
    const DISQUALIFY_STATUS = 'canceled';
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
     * DisqualifyLead constructor.
     *
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
    )
    {
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
    public function process(Lead $lead)
    {
        try {
            $this->save($lead->setStatus($this->getDisqualifyStatus()));
            $this->session->getFlashBag()->add('success', 'Saved');
        } catch (\Exception $e) {
            $this->session->getFlashBag()->add('error', 'Not saved');
        }

        return new RedirectResponse($this->router->generate(self::LEAD_VIEW_ROUTE, ['id' => $lead->getId()]));
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
     * @return LeadStatus
     */
    protected function getDisqualifyStatus()
    {
        $repository = $this->manager->getRepository('OroCRMSalesBundle:LeadStatus');

        $result = $repository->createQueryBuilder('ls')
                             ->where('ls.name = :lead_status_name')
                             ->setParameter(':lead_status_name', self::DISQUALIFY_STATUS)
                             ->getQuery()
                             ->getSingleResult();

        return $result;
    }
}
