<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\MagentoBundle\Entity\Product;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

/**
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * @Route("/", name="orocrm_magento_product_index")
     * @AclAncestor("orocrm_magento_product_view")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/view/{id}", name="orocrm_magento_product_view", requirements={"id"="\d+"}))
     * @Acl(
     *      id="orocrm_magento_product_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMMagentoBundle:Product"
     * )
     * @Template
     */
    public function viewAction(Product $customer)
    {
        return ['entity' => $customer];
    }

    /**
     * @Route("/info/{id}", name="orocrm_magento_product_info", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_product_view")
     * @Template
     */
    public function infoAction(Product $customer)
    {
        return ['entity' => $customer];
    }
}
