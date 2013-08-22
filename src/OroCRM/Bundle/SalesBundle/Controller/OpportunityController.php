<?php

namespace OroCRM\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Datagrid\OpportunityDatagridManager;

/**
 * @Route("/opportunity")
 * @Acl(
 *      id="orocrm_opportunity",
 *      name="Opportunity manipulation",
 *      description="Opportunity manipulation",
 *      parent="root"
 * )
 */
class OpportunityController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_opportunity_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_opportunity_view",
     *      name="View opportunity",
     *      description="View opportunity",
     *      parent="orocrm_opportunity"
     * )
     */
    public function viewAction(Opportunity $entity)
    {
        return array(
            'entity' => $entity,
        );
    }

    /**
     * @Route("/info/{id}", name="orocrm_opportunity_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_opportunity_view")
     */
    public function infoAction(Opportunity $entity)
    {
        return array(
            'entity' => $entity
        );
    }

    /**
     * @Route("/create", name="orocrm_opportunity_create")
     * @Template("OroCRMSalesBundle:Opportunity:update.html.twig")
     * @Acl(
     *      id="orocrm_opportunity_create",
     *      name="Create opportunity",
     *      description="Create opportunity",
     *      parent="orocrm_opportunity"
     * )
     */
    public function createAction()
    {
        $entity = new Opportunity();
        $defaultStatus = $this->getDoctrine()->getManager()->find('OroCRMSalesBundle:OpportunityStatus', 'in_progress');
        $entity->setStatus($defaultStatus);

        return $this->updateAction($entity);
    }

    /**
     * @Route("/update/{id}", name="orocrm_opportunity_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_opportunity_update",
     *      name="Update opportunity",
     *      description="Update opportunity",
     *      parent="orocrm_opportunity"
     * )
     */
    public function updateAction(Opportunity $entity)
    {
        if ($this->get('orocrm_sales.opportunity.form.handler')->process($entity)) {
            $this->getFlashBag()->add('success', 'Opportunity successfully saved');

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'orocrm_opportunity_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route' => 'orocrm_opportunity_index',
                )
            );
        }

        return array(
            'entity' => $entity,
            'form'   => $this->get('orocrm_sales.opportunity.form')->createView(),
        );
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_opportunity_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @Acl(
     *      id="orocrm_opportunity_list",
     *      name="View list of opportunities",
     *      description="View list of opportunities",
     *      parent="orocrm_opportunity"
     * )
     */
    public function indexAction()
    {
        /** @var OpportunityDatagridManager $datagridManager */
        $datagridManager = $this->get('orocrm_sales.opportunity.datagrid_manager');
        $datagridView = $datagridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array('datagrid' => $datagridView);
    }

    /**
     * @return FlashBag
     */
    protected function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }
}
