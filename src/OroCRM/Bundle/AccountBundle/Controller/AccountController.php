<?php

namespace OroCRM\Bundle\AccountBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class AccountController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_account_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_account_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMAccountBundle:Account"
     * )
     */
    public function viewAction(Account $account)
    {
        return ['entity'  => $account];
    }

    /**
     * Create account form
     *
     * @Route("/create", name="orocrm_account_create")
     * @Template("OroCRMAccountBundle:Account:update.html.twig")
     * @Acl(
     *      id="orocrm_account_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMAccountBundle:Account"
     * )
     */
    public function createAction()
    {
        return $this->update();
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="orocrm_account_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_account_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMAccountBundle:Account"
     * )
     */
    public function updateAction(Account $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_account_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @AclAncestor("orocrm_account_view")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @return ApiEntityManager
     */
    protected function getManager()
    {
        return $this->get('orocrm_account.account.manager.api');
    }

    /**
     * @param Account $entity
     * @return array
     */
    protected function update(Account $entity = null)
    {
        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }

        if ($this->get('orocrm_account.form.handler.account')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.account.controller.account.saved.message')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'orocrm_account_update', 'parameters' => ['id' => $entity->getId()]],
                ['route' => 'orocrm_account_view', 'parameters' => ['id' => $entity->getId()]],
                $entity
            );
        }

        return array(
            'form'     => $this->get('orocrm_account.form.account')->createView()
        );
    }

    /**
     * @Route(
     *      "/contact/select/{id}",
     *      name="orocrm_account_contact_select",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @Template
     * @AclAncestor("orocrm_contact_view")
     */
    public function contactDatagridAction(Account $entity = null)
    {
        return [
            'account' => $entity ? $entity->getId() : $entity
        ];
    }

    /**
     * @Route("/widget/info/{id}", name="orocrm_account_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_account_view")
     * @Template
     */
    public function infoAction(Account $account)
    {
        return [
            'entity'  => $account
        ];
    }

    /**
     * @Route("/widget/contacts/{id}", name="orocrm_account_widget_contacts", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_contact_view")
     * @Template
     */
    public function contactsAction(Account $account)
    {
        $defaultContact = $account->getDefaultContact();
        $contacts = $account->getContacts();
        $notDefaultContacts = array();
        if (!isset($defaultContact)) {
            $defaultContact = $contacts->count() > 0 ? $contacts[0] : null;
        }
        /**
         * @var Contact $contact
         */
        foreach ($contacts as $contact) {
            if ($contact->getId() == $defaultContact->getId()) {
                continue;
            }
            $notDefaultContacts[] = $contact;
        }

        return array('entity'             => $account,
                     'defaultContact'     => $defaultContact,
                     'notDefaultContacts' => $notDefaultContacts
        );
    }
}
