<?php

namespace Oro\Bundle\CaseBundle\Controller;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Form\Handler\CaseEntityHandler;
use Oro\Bundle\CaseBundle\Model\CaseEntityManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD controller for Cases.
 */
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
     * @Template("@OroCase/Case/update.html.twig")
     */
    public function createAction(Request $request)
    {
        $case = $this->get(CaseEntityManager::class)->createCase();

        return $this->update($case, $request);
    }

    /**
     * @Route("/update/{id}", name="oro_case_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("oro_case_update")
     */
    public function updateAction(CaseEntity $case, Request $request)
    {
        return $this->update($case, $request);
    }

    /**
     * @param CaseEntity $case
     * @param Request $request
     * @return array
     */
    protected function update(CaseEntity $case, Request $request)
    {
        if ($this->get(CaseEntityHandler::class)->process($case)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->get(TranslatorInterface::class)->trans('oro.case.message.saved')
            );

            return $this->get(Router::class)->redirect($case);
        }

        return [
            'entity' => $case,
            'form'   => $this->get('oro_case.form.entity')->createView()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                Router::class,
                CaseEntityManager::class,
                CaseEntityHandler::class,
                'oro_case.form.entity' => Form::class,
            ]
        );
    }
}
