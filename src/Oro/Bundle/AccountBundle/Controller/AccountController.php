<?php

namespace Oro\Bundle\AccountBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Event\CollectAccountWebsiteActivityCustomersEvent;
use Oro\Bundle\AccountBundle\Form\Handler\AccountHandler;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD for accounts.
 */
class AccountController extends AbstractController
{
    #[Route(path: '/view/{id}', name: 'oro_account_view', requirements: ['id' => '\d+'])]
    #[Template]
    #[Acl(id: 'oro_account_view', type: 'entity', class: Account::class, permission: 'VIEW')]
    public function viewAction(Account $account): array
    {
        $channels = $this->container->get('doctrine')
            ->getRepository(Channel::class)
            ->findBy([], ['channelType' => 'ASC', 'name' => 'ASC']);

        $event = new CollectAccountWebsiteActivityCustomersEvent($account->getId());
        $this->container->get(EventDispatcherInterface::class)->dispatch($event);

        return [
            'entity' => $account,
            'channels' => $channels,
            'customers' => $event->getCustomers(),
        ];
    }

    /**
     * Create account form
     */
    #[Route(path: '/create', name: 'oro_account_create')]
    #[Template('@OroAccount/Account/update.html.twig')]
    #[Acl(id: 'oro_account_create', type: 'entity', class: Account::class, permission: 'CREATE')]
    public function createAction(): array|RedirectResponse
    {
        return $this->update();
    }

    /**
     * Edit user form
     */
    #[Route(path: '/update/{id}', name: 'oro_account_update', requirements: ['id' => '\d+'])]
    #[Template]
    #[Acl(id: 'oro_account_update', type: 'entity', class: Account::class, permission: 'EDIT')]
    public function updateAction(Account $entity): array|RedirectResponse
    {
        return $this->update($entity);
    }

    #[Route(
        path: '/{_format}',
        name: 'oro_account_index',
        requirements: ['_format' => 'html|json'],
        defaults: ['_format' => 'html']
    )]
    #[Template]
    #[AclAncestor('oro_account_view')]
    public function indexAction(): array
    {
        return [
            'entity_class' => Account::class
        ];
    }

    protected function getManager(): ApiEntityManager
    {
        return $this->container->get(ApiEntityManager::class);
    }

    protected function update(?Account $entity = null): array|RedirectResponse
    {
        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }

        return $this->container->get(UpdateHandlerFacade::class)->update(
            $entity,
            $this->container->get('oro_account.form.account'),
            $this->container->get(TranslatorInterface::class)->trans('oro.account.controller.account.saved.message'),
            null,
            $this->container->get(AccountHandler::class)
        );
    }

    #[Route(
        path: '/widget/contacts/{id}',
        name: 'oro_account_widget_contacts_info',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0]
    )]
    #[Template]
    #[AclAncestor('oro_contact_view')]
    public function contactsInfoAction(?Account $account = null): array
    {
        return [
            'account' => $account
        ];
    }

    #[Route(path: '/widget/info/{id}', name: 'oro_account_widget_info', requirements: ['id' => '\d+'])]
    #[Template]
    #[AclAncestor('oro_account_view')]
    public function infoAction(Account $account): array
    {
        return [
            'account' => $account
        ];
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                EventDispatcherInterface::class,
                TranslatorInterface::class,
                ApiEntityManager::class,
                'oro_account.form.account' => Form::class,
                AccountHandler::class,
                UpdateHandlerFacade::class,
                'doctrine' => ManagerRegistry::class
            ]
        );
    }
}
