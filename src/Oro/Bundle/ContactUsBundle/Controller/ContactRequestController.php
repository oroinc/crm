<?php

namespace Oro\Bundle\ContactUsBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Oro\Bundle\ContactUsBundle\Form\Type\ContactRequestEditType;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Contact Request Controller
 */
class ContactRequestController extends AbstractController
{
    /**
     * @param ContactRequest $contactRequest
     *
     * @return array
     */
    #[Route(path: '/view/{id}', name: 'oro_contactus_request_view', requirements: ['id' => '\d+'])]
    #[Template]
    #[Acl(id: 'oro_contactus_request_view', type: 'entity', class: ContactRequest::class, permission: 'VIEW')]
    public function viewAction(ContactRequest $contactRequest)
    {
        return [
            'entity' => $contactRequest
        ];
    }

    #[Route(name: 'oro_contactus_request_index')]
    #[Template('@OroContactUs/ContactRequest/index.html.twig')]
    #[AclAncestor('oro_contactus_request_view')]
    public function indexAction()
    {
        return [
            'entity_class' => ContactRequest::class
        ];
    }

    #[Route(path: '/info/{id}', name: 'oro_contactus_request_info', requirements: ['id' => '\d+'])]
    #[Template('@OroContactUs/ContactRequest/widget/info.html.twig')]
    #[AclAncestor('oro_contactus_request_view')]
    public function infoAction(ContactRequest $contactRequest)
    {
        return [
            'entity' => $contactRequest
        ];
    }

    #[Route(path: '/update/{id}', name: 'oro_contactus_request_update', requirements: ['id' => '\d+'])]
    #[Template('@OroContactUs/ContactRequest/update.html.twig')]
    #[Acl(id: 'oro_contactus_request_edit', type: 'entity', class: ContactRequest::class, permission: 'EDIT')]
    public function updateAction(
        ContactRequest $contactRequest,
        UpdateHandlerFacade $formHandler,
        TranslatorInterface $translator
    ) {
        return $this->update($contactRequest, $formHandler, $translator);
    }

    #[Route(path: '/create', name: 'oro_contactus_request_create')]
    #[Template('@OroContactUs/ContactRequest/update.html.twig')]
    #[Acl(id: 'oro_contactus_request_create', type: 'entity', class: ContactRequest::class, permission: 'CREATE')]
    public function createAction(
        UpdateHandlerFacade $formHandler,
        TranslatorInterface $translator
    ) {
        return $this->update(new ContactRequest(), $formHandler, $translator);
    }

    #[Route(
        path: '/delete/{id}',
        name: 'oro_contactus_request_delete',
        requirements: ['id' => '\d+'],
        methods: ['DELETE']
    )]
    #[Acl(id: 'oro_contactus_request_delete', type: 'entity', class: ContactRequest::class, permission: 'DELETE')]
    #[CsrfProtection()]
    public function deleteAction(ContactRequest $contactRequest): JsonResponse
    {
        $em = $this->container->get('doctrine')->getManagerForClass(ContactRequest::class);

        $em->remove($contactRequest);
        $em->flush();

        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @return array|RedirectResponse
     */
    protected function update(
        ContactRequest $contactRequest,
        UpdateHandlerFacade $formHandler,
        TranslatorInterface $translator
    ) {
        return $formHandler->update(
            $contactRequest,
            $this->createForm(ContactRequestEditType::class, $contactRequest),
            $translator->trans('oro.contactus.contactrequest.entity.saved')
        );
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                UpdateHandlerFacade::class,
                TranslatorInterface::class,
                'doctrine' => ManagerRegistry::class,
            ]
        );
    }
}
