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
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("orocrm_sales_b2bcustomer_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orocrm_sales.customer.class')
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
        return array(
            'entity' => $customer
        );
    }
}
