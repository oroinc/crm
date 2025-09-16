<?php

namespace Oro\Bundle\ContactBundle\Controller;

use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\ContactBundle\Form\Handler\GroupHandler;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\UIBundle\Route\Router;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The controller for Group entity.
 */
#[Route(path: '/group')]
class GroupController extends AbstractController
{
    /**
     * Create group form
     *
     * @param Request $request
     * @return array|RedirectResponse
     */
    #[Route(path: '/create', name: 'oro_contact_group_create')]
    #[Template('@OroContact/Group/update.html.twig')]
    #[Acl(id: 'oro_contact_group_create', type: 'entity', class: Group::class, permission: 'CREATE')]
    public function createAction(Request $request)
    {
        return $this->update($request, new Group());
    }

    /**
     * Update group form
     *
     * @param Request $request
     * @param Group $entity
     * @return array|RedirectResponse
     */
    #[Route(
        path: '/update/{id}',
        name: 'oro_contact_group_update',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0]
    )]
    #[Template('@OroContact/Group/update.html.twig')]
    #[Acl(id: 'oro_contact_group_update', type: 'entity', class: Group::class, permission: 'EDIT')]
    public function updateAction(Request $request, Group $entity)
    {
        return $this->update($request, $entity);
    }

    #[Route(
        path: '/{_format}',
        name: 'oro_contact_group_index',
        requirements: ['_format' => 'html|json'],
        defaults: ['_format' => 'html']
    )]
    #[Template('@OroContact/Group/index.html.twig')]
    #[Acl(id: 'oro_contact_group_view', type: 'entity', class: Group::class, permission: 'VIEW')]
    public function indexAction()
    {
        return [
            'entity_class' => Group::class
        ];
    }

    /**
     * @param Group $entity
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    protected function update(Request $request, Group $entity)
    {
        if ($this->container->get(GroupHandler::class)->process($entity)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)
                    ->trans('oro.contact.controller.contact_group.saved.message')
            );

            if (!$request->get('_widgetContainer')) {
                return $this->container->get(Router::class)->redirect($entity);
            }
        }

        return [
            'entity'           => $entity,
            'form'             => $this->container->get('oro_contact.form.group')->createView(),
            'showContactsGrid' => count($this->container->get(ApiEntityManager::class)->getList()) ? true : false
        ];
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                Router::class,
                ApiEntityManager::class,
                GroupHandler::class,
                'oro_contact.form.group' => Form::class,
            ]
        );
    }
}
