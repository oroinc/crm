<?php

namespace OroCRM\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

/**
 * @Route("/b2bcustomer")
 */
class B2bCustomerController extends Controller
{
    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_sales_b2bcustomer_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format"="html"}
     * )
     * @Template
     * @AclAncestor("orocrm_sales_b2bcustomer_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orocrm_sales.b2bcustomer.entity.class')
        ];
    }

    /**
     * @Route("/view/{id}", name="orocrm_sales_b2bcustomer_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_sales_b2bcustomer_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMSalesBundle:B2bCustomer"
     * )
     */
    public function viewAction(B2bCustomer $customer)
    {
        return [
            'entity' => $customer
        ];
    }

    /**
     * @Route("/widget/info/{id}", name="orocrm_salses_b2bcustomer_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_sales_b2bcustomer_view")
     * @Template()
     */
    public function infoAction(B2bCustomer $customer)
    {
        return [
            'entity' => $customer
        ];
    }

    /**
     * @Route("/widget/b2bcustomer-leads/{id}", name="orocrm_sales_b2bcustomer_widget_leads", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_sales_lead_view")
     * @Template
     */
    public function b2bCustomerLeadsAction(B2bCustomer $customer)
    {
        return [
            'entity' => $customer
        ];
    }

    /**
     * @Route(
     *      "/widget/b2bcustomer-opportunities/{id}",
     *      name="orocrm_sales_b2bcustomer_widget_opportunities",
     *      requirements={"id"="\d+"}
     * )
     * @AclAncestor("orocrm_sales_opportunity_view")
     * @Template
     */
    public function b2bCustomerOpportunitiesAction(B2bCustomer $customer)
    {
        return [
            'entity' => $customer
        ];
    }
}
