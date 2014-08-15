<?php

namespace OroCRM\Bundle\MarketingListBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/marketing-list")
 */
class MarketingListController extends Controller
{
    /**
     * @Route("/", name="orocrm_marketing_list_index")
     * @AclAncestor("orocrm_marketing_list_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orocrm_marketing_list.entity.class')
        ];
    }
}
