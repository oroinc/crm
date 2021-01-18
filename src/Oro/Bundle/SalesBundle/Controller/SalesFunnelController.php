<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Oro\Bundle\SalesBundle\Entity\SalesFunnel;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The controller for SalesFunnel entity.
 * @Route("/salesfunnel")
 */
class SalesFunnelController extends AbstractController
{
    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_sales_salesfunnel_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("oro_sales_salesfunnel_view")
     */
    public function indexAction()
    {
        return array(
            'entity_class' => SalesFunnel::class
        );
    }

    /**
     * @Route("/view/{id}", name="oro_sales_salesfunnel_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_sales_salesfunnel_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroSalesBundle:SalesFunnel"
     * )
     */
    public function viewAction(SalesFunnel $entity)
    {
        return array(
            'entity' => $entity,
        );
    }

    /**
     * @Route("/info/{id}", name="oro_sales_salesfunnel_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("oro_sales_salesfunnel_view")
     */
    public function infoAction(SalesFunnel $entity)
    {
        return array(
            'entity'  => $entity
        );
    }

    /**
     * @Route("/create", name="oro_sales_salesfunnel_create")
     * @Template("OroSalesBundle:SalesFunnel:update.html.twig")
     * @Acl(
     *      id="oro_sales_salesfunnel_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroSalesBundle:SalesFunnel"
     * )
     */
    public function createAction()
    {
        $entity = new SalesFunnel();

        return $this->update($entity);
    }

    /**
     * @Route("/update/{id}", name="oro_sales_salesfunnel_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_sales_salesfunnel_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroSalesBundle:SalesFunnel"
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
        if ($this->get('oro_sales.salesfunnel.form.handler')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.sales.controller.sales_funnel.saved.message')
            );

            return $this->get('oro_ui.router')->redirect($entity);
        }

        return array(
            'entity' => $entity,
            'form' => $this->get('oro_sales.salesfunnel.form')->createView(),
        );
    }
}
