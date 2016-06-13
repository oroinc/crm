<?php

namespace OroCRM\Bundle\CaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
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
     * @AclAncestor("orocrm_case_view")
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
     * @Route("/widget/contact-cases/{id}", name="orocrm_case_contact_widget_cases", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_case_view")
     * @Template
     */
    public function contactCasesAction(Contact $contact)
    {
        return [
            'contact' => $contact
        ];
    }

    /**
     * Create case form
     *
     * @Route("/create", name="orocrm_case_create")
     * @AclAncestor("orocrm_case_create")
     * @Template("OroCRMCaseBundle:Case:update.html.twig")
     */
    public function createAction()
    {
        $case = $this->get('orocrm_case.manager')->createCase();

        return $this->update($case);
    }

    /**
     * @Route("/update/{id}", name="orocrm_case_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_case_update")
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
                $this->get('translator')->trans('orocrm.case.message.saved')
            );

            return $this->get('oro_ui.router')->redirect($case);
        }

        return array(
            'entity' => $case,
            'form'   => $this->get('orocrm_case.form.entity')->createView()
        );
    }
}
