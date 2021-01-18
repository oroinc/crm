<?php

namespace Oro\Bundle\CaseBundle\Controller;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CaseController extends AbstractController
{
    /**
     * @Route(name="oro_case_index")
     * @Template
     * @AclAncestor("oro_case_view")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/view/{id}", name="oro_case_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("oro_case_view")
     */
    public function viewAction(CaseEntity $case)
    {
        return [
            'entity' => $case
        ];
    }

    /**
     * @Route("/widget/account-cases/{id}", name="oro_case_account_widget_cases", requirements={"id"="\d+"})
     * @AclAncestor("oro_case_view")
     * @Template
     */
    public function accountCasesAction(Account $account)
    {
        return [
            'account' => $account
        ];
    }

    /**
     * @Route("/widget/contact-cases/{id}", name="oro_case_contact_widget_cases", requirements={"id"="\d+"})
     * @AclAncestor("oro_case_view")
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
     * @Route("/create", name="oro_case_create")
     * @AclAncestor("oro_case_create")
     * @Template("OroCaseBundle:Case:update.html.twig")
     */
    public function createAction()
    {
        $case = $this->get('oro_case.manager')->createCase();

        return $this->update($case);
    }

    /**
     * @Route("/update/{id}", name="oro_case_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("oro_case_update")
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
        if ($this->get('oro_case.form.handler.entity')->process($case)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.case.message.saved')
            );

            return $this->get('oro_ui.router')->redirect($case);
        }

        return array(
            'entity' => $case,
            'form'   => $this->get('oro_case.form.entity')->createView()
        );
    }
}
