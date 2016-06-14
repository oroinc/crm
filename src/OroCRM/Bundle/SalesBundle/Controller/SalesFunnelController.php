<?php

namespace OroCRM\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\SalesBundle\Entity\SalesFunnel;

/**
 * @Route("/salesfunnel")
 */
class SalesFunnelController extends Controller
{
    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_sales_salesfunnel_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("orocrm_sales_salesfunnel_view")
     */
    public function indexAction()
    {
        return array(
            'entity_class' => $this->container->getParameter('orocrm_sales.salesfunnel.entity.class')
        );
    }

    /**
     * @Route("/view/{id}", name="orocrm_sales_salesfunnel_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_sales_salesfunnel_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMSalesBundle:SalesFunnel"
     * )
     */
    public function viewAction(SalesFunnel $entity)
    {
        return array(
            'entity' => $entity,
        );
    }

    /**
     * @Route("/info/{id}", name="orocrm_sales_salesfunnel_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_sales_salesfunnel_view")
     */
    public function infoAction(SalesFunnel $entity)
    {
        return array(
            'entity'  => $entity
        );
    }

    /**
     * @Route("/create", name="orocrm_sales_salesfunnel_create")
     * @Template("OroCRMSalesBundle:SalesFunnel:update.html.twig")
     * @Acl(
     *      id="orocrm_sales_salesfunnel_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMSalesBundle:SalesFunnel"
     * )
     */
    public function createAction()
    {
        $entity = new SalesFunnel();

        return $this->update($entity);
    }

    /**
     * @Route("/update/{id}", name="orocrm_sales_salesfunnel_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_sales_salesfunnel_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMSalesBundle:SalesFunnel"
     * )
     */
    public function updateAction(SalesFunnel $entity)
    {
        return $this->update($entity);
    }

    /**
     * @param  SalesFunnel $entity
     * @return array
     */
    protected function update(SalesFunnel $entity)
    {
        if ($this->get('orocrm_sales.salesfunnel.form.handler')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.sales.controller.sales_funnel.saved.message')
            );

            return $this->get('oro_ui.router')->redirect($entity);
        }

        return array(
            'entity' => $entity,
            'form' => $this->get('orocrm_sales.salesfunnel.form')->createView(),
        );
    }
}
