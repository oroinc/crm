<?php

namespace Oro\Bundle\AccountBundle\Controller;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Event\CollectAccountWebsiteActivityCustomersEvent;
use Oro\Bundle\AccountBundle\Form\Handler\AccountHandler;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD for accounts.
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/view/{id}", name="oro_account_view", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_account_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroAccountBundle:Account"
     * )
     * @Template()
     */
    public function viewAction(Account $account): array
    {
        $channels = $this->getDoctrine()
            ->getRepository('OroChannelBundle:Channel')
            ->findBy([], ['channelType' => 'ASC', 'name' => 'ASC']);

        $event = new CollectAccountWebsiteActivityCustomersEvent($account->getId());
        $this->get(EventDispatcherInterface::class)->dispatch($event);

        return [
            'entity' => $account,
            'channels' => $channels,
            'customers' => $event->getCustomers(),
        ];
    }

    /**
     * Create account form
     *
     * @Route("/create", name="oro_account_create")
     * @Acl(
     *      id="oro_account_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroAccountBundle:Account"
     * )
     * @Template("@OroAccount/Account/update.html.twig")
     */
    public function createAction(): array|RedirectResponse
    {
        return $this->update();
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="oro_account_update", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_account_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroAccountBundle:Account"
     * )
     * @Template()
     */
    public function updateAction(Account $entity): array|RedirectResponse
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_account_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @AclAncestor("oro_account_view")
     * @Template
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => Account::class
        ];
    }

    protected function getManager(): ApiEntityManager
    {
        return $this->get(ApiEntityManager::class);
    }

    protected function update(Account $entity = null): array|RedirectResponse
    {
        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }

        return $this->get(UpdateHandlerFacade::class)->update(
            $entity,
            $this->get('oro_account.form.account'),
            $this->get(TranslatorInterface::class)->trans('oro.account.controller.account.saved.message'),
            null,
            $this->get(AccountHandler::class)
        );
    }

    /**
     * @Route(
     *      "/widget/contacts/{id}",
     *      name="oro_account_widget_contacts_info",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @AclAncestor("oro_contact_view")
     * @Template()
     */
    public function contactsInfoAction(Account $account = null): array
    {
        return [
            'account' => $account
        ];
    }

    /**
     * @Route("/widget/info/{id}", name="oro_account_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("oro_account_view")
     * @Template()
     */
    public function infoAction(Account $account): array
    {
        return [
            'account' => $account
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                EventDispatcherInterface::class,
                TranslatorInterface::class,
                ApiEntityManager::class,
                'oro_account.form.account' => Form::class,
                AccountHandler::class,
                UpdateHandlerFacade::class
            ]
        );
    }
}
