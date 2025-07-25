<?php

namespace Oro\Bundle\ContactUsBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\ContactUsBundle\Form\Type\ContactReasonType;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD controller for ContactReason entity
 */
class ContactReasonController extends AbstractController
{
    #[Route(path: '/', name: 'oro_contactus_reason_index')]
    #[Template]
    #[Acl(id: 'oro_contactus_reason_view', type: 'entity', class: ContactReason::class, permission: 'VIEW')]
    public function indexAction(): array
    {
        return [
            'entity_class' => ContactReason::class
        ];
    }

    #[Route(path: '/create', name: 'oro_contactus_reason_create')]
    #[Template('@OroContactUs/ContactReason/update.html.twig')]
    #[Acl(id: 'oro_contactus_reason_create', type: 'entity', class: ContactReason::class, permission: 'CREATE')]
    public function createAction(): array|RedirectResponse
    {
        return $this->update(new ContactReason());
    }

    #[Route(path: '/update/{id}', name: 'oro_contactus_reason_update', requirements: ['id' => '\d+'])]
    #[ParamConverter('contactReason', options: ['repository_method' => 'getContactReason'])]
    #[Template]
    #[Acl(id: 'oro_contactus_reason_update', type: 'entity', class: ContactReason::class, permission: 'EDIT')]
    public function updateAction(ContactReason $contactReason): array|RedirectResponse
    {
        return $this->update($contactReason);
    }

    protected function update(ContactReason $contactReason): array|RedirectResponse
    {
        return $this->container->get(UpdateHandlerFacade::class)->update(
            $contactReason,
            $this->createForm(ContactReasonType::class, $contactReason),
            $this->container->get(TranslatorInterface::class)->trans('oro.contactus.contactreason.saved')
        );
    }

    #[Route(
        path: '/delete/{id}',
        name: 'oro_contactus_reason_delete',
        requirements: ['id' => '\d+'],
        methods: ['DELETE']
    )]
    #[ParamConverter('contactReason', options: ['repository_method' => 'getContactReason'])]
    #[Acl(id: 'oro_contactus_reason_delete', type: 'entity', class: ContactReason::class, permission: 'DELETE')]
    #[CsrfProtection()]
    public function deleteAction(ContactReason $contactReason): JsonResponse
    {
        $em = $this->container->get('doctrine')->getManagerForClass(ContactReason::class);
        $em->remove($contactReason);
        $em->flush();

        return new JsonResponse('', Response::HTTP_OK);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                UpdateHandlerFacade::class,
                'doctrine' => ManagerRegistry::class,
            ]
        );
    }
}
