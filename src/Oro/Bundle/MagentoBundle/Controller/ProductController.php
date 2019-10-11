<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Oro\Bundle\MagentoBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * @Route("/", name="oro_magento_product_index")
     * @AclAncestor("oro_magento_product_view")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/view/{id}", name="oro_magento_product_view", requirements={"id"="\d+"}))
     * @Acl(
     *      id="oro_magento_product_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroMagentoBundle:Product"
     * )
     * @Template
     */
    public function viewAction(Product $customer)
    {
        return ['entity' => $customer];
    }

    /**
     * @Route("/info/{id}", name="oro_magento_product_info", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_product_view")
     * @Template
     */
    public function infoAction(Product $customer)
    {
        return ['entity' => $customer];
    }
}
