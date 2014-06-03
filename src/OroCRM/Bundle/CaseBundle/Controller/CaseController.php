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
     * @AclAncestor("orocrm_account_view")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="orocrm_case_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_case_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMCaseBundle:CaseEntity"
     * )
     */
    public function viewAction()
    {
        return array();
    }

    /**
     * Create account form
     *
     * @Route("/create", name="orocrm_case_create")
     * @Acl(
     *      id="orocrm_case_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMCaseBundle:CaseEntity"
     * )
     * @Template("OroCRMCaseBundle:Case:update.html.twig")
     */
    public function createAction()
    {
        return array();
    }

    /**
     * @Route("/update/{id}", name="orocrm_case_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_case_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMCaseBundle:CaseEntity"
     * )
     */
    public function updateAction()
    {
        return array();
    }
}
