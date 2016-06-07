<?php

namespace OroCRM\Bundle\AccountBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class AccountController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_account_view", requirements={"id"="\d+"})
     * @Acl(
     *      id="orocrm_account_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMAccountBundle:Account"
     * )
     * @Template()
     */
    public function viewAction(Account $account)
    {
        $channels = $this->getDoctrine()
            ->getRepository('OroCRMChannelBundle:Channel')
            ->findBy(['status' => Channel::STATUS_ACTIVE], ['channelType' => 'ASC', 'name' => 'ASC']);

        return array('entity' => $account, 'channels' => $channels);
    }

    /**
     * Create account form
     *
     * @Route("/create", name="orocrm_account_create")
     * @Acl(
     *      id="orocrm_account_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMAccountBundle:Account"
     * )
     * @Template("OroCRMAccountBundle:Account:update.html.twig")
     */
    public function createAction()
    {
        return $this->update();
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="orocrm_account_update", requirements={"id"="\d+"})
     * @Acl(
     *      id="orocrm_account_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMAccountBundle:Account"
     * )
     * @Template()
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
        return [
            'entity_class' => $this->container->getParameter('orocrm_account.account.entity.class')
        ];
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

        return $this->get('oro_form.model.update_handler')->update(
            $entity,
            $this->get('orocrm_account.form.account'),
            $this->get('translator')->trans('orocrm.account.controller.account.saved.message'),
            $this->get('orocrm_account.form.handler.account')
        );
    }

    /**
     * @Route(
     *      "/widget/contacts/{id}",
     *      name="orocrm_account_widget_contacts_info",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @AclAncestor("orocrm_contact_view")
     * @Template()
     */
    public function contactsInfoAction(Account $account = null)
    {
        return [
            'account' => $account
        ];
    }

    /**
     * @Route("/widget/info/{id}", name="orocrm_account_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_account_view")
     * @Template()
     */
    public function infoAction(Account $account)
    {
        return [
            'account' => $account
        ];
    }
}
