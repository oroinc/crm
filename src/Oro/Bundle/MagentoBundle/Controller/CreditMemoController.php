<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;

/**
 * @Route("/credit-memo")
 */
class CreditMemoController extends Controller
{
    /**
     * @Route("/", name="oro_magento_credit_memo_index")
     * @AclAncestor("oro_magento_credit_memo_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('oro_magento.credit_memo.entity.class')
        ];
    }

    /**
     * @Route("/view/{id}", name="oro_magento_credit_memo_view", requirements={"id"="\d+"}))
     * @Acl(
     *      id="oro_magento_credit_memo_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroMagentoBundle:CreditMemo"
     * )
     * @Template
     * @param CreditMemo $entity
     *
     * @return array
     */
    public function viewAction(CreditMemo $entity)
    {
        return ['entity' => $entity];
    }

    /**
     * @Route("/info/{id}", name="oro_magento_credit_memo_widget_info", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_credit_memo_view")
     * @Template
     * @param CreditMemo $entity
     *
     * @return array
     */
    public function infoAction(CreditMemo $entity)
    {
        return ['entity' => $entity];
    }

    /**
     * @Route("/widget/grid/{id}", name="oro_magento_credit_memo_widget_items", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_credit_memo_view")
     * @Template
     * @param CreditMemo $entity
     * @return array
     */
    public function itemsAction(CreditMemo $entity)
    {
        return ['entity' => $entity];
    }
}
