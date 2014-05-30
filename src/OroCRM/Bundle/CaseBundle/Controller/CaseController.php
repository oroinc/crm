<?php

namespace OroCRM\Bundle\CaseBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class CaseController extends Controller
{
    /**
     * @Route(name="orocrm_case_index")
     * @Template
     * @Acl(
     *      id="orocrm_case_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMCaseBundle:CaseEntity"
     * )
     */
    public function indexAction()
    {
        return array();
    }
}
