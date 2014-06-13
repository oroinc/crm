<?php

namespace OroCRM\Bundle\CaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\AccountBundle\Entity\Account;

use OroCRM\Bundle\CaseBundle\Entity\CasePriority;
use OroCRM\Bundle\CaseBundle\Entity\CaseEntity;

class CaseController extends Controller
{
    /**
     * @Route(name="orocrm_case_index")
     * @Template
     * @AclAncestor("orocrm_case_view")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/view/{id}", name="orocrm_case_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *     id="orocrm_case_view",
     *     type="entity",
     *     permission="VIEW",
     *     class="OroCRMCaseBundle:CaseEntity"
     * )
     */
    public function viewAction(CaseEntity $case)
    {
        return [
            'entity' => $case
        ];
    }

    /**
     * @Route("/widget/account-cases/{id}", name="orocrm_case_account_widget_cases", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_case_view")
     * @Template
     */
    public function accountCasesAction(Account $account)
    {
        return [
            'account' => $account
        ];
    }

    /**
     * Create case form
     *
     * @Route("/create", name="orocrm_case_create")
     * @Acl(
     *     id="orocrm_case_create",
     *     type="entity",
     *     permission="CREATE",
     *     class="OroCRMCaseBundle:CaseEntity"
     * )
     * @Template("OroCRMCaseBundle:Case:update.html.twig")
     */
    public function createAction()
    {
        $case = new CaseEntity();

        $priority = $this->getDoctrine()->getRepository('OroCRMCaseBundle:CasePriority')
            ->findOneBy(array('name' => CasePriority::PRIORITY_NORMAL));

        $case->setPriority($priority);

        return $this->update($case);
    }

    /**
     * @Route("/update/{id}", name="orocrm_case_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *     id="orocrm_case_update",
     *     type="entity",
     *     permission="EDIT",
     *     class="OroCRMCaseBundle:CaseEntity"
     * )
     */
    public function updateAction(CaseEntity $case)
    {
        return $this->update($case);
    }

    /**
     * @param CaseEntity $case
     * @return array
     */
    protected function update(CaseEntity $case)
    {
        if ($this->get('orocrm_case.form.handler.entity')->process($case)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.case.saved_message')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'orocrm_case_update', 'parameters' => ['id' => $case->getId()]],
                ['route' => 'orocrm_case_view', 'parameters' => ['id' => $case->getId()]]
            );
        }

        return array(
            'entity' => $case,
            'form'   => $this->get('orocrm_sales.lead.form')->createView()
        );
    }
}
