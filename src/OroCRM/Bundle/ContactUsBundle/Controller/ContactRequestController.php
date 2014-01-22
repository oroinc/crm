<?php

namespace OroCRM\Bundle\ContactUsBundle\Controller;


use FOS\Rest\Util\Codes;
use OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest;
use OroCRM\Bundle\ContactUsBundle\Form\Type\ContactRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation as Security;
use Symfony\Component\HttpFoundation\JsonResponse;

class ContactRequestController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_contact_request_view", requirements={"id"="\d+"})
     * @Template
     * @Security\Acl(
     *      id="orocrm_contact_request_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMContactUsBundle:ContactRequest"
     * )
     */
    public function viewAction(ContactRequest $contactRequest)
    {
        return [
            'entity' => $contactRequest
        ];
    }


    /**
     * @Route(name="orocrm_contact_request_index")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/info/{id}", name="orocrm_contact_request_info", requirements={"id"="\d+"})
     * @Template
     */
    public function infoAction(ContactRequest $contactRequest)
    {
        return [
            'entity' => $contactRequest
        ];
    }

    /**
     * @Route("/update/{id}", name="orocrm_contact_request_update", requirements={"id"="\d+"})
     * @Template
     */
    public function updateAction(ContactRequest $contactRequest)
    {
        return $this->update($contactRequest);
    }

    /**
     * @Route("/create", name="orocrm_contact_request_create")
     * @Template("OroCRMContactUsBundle:ContactRequest:update.html.twig")
     */
    public function createAction()
    {
        return $this->update();
    }

    /**
     * @Route("/delete/{id}", name="orocrm_contact_request_delete", requirements={"id"="\d+"})
     */
    public function deleteAction(ContactRequest $contactRequest)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $em->remove($contactRequest);
        $em->flush();

        return new JsonResponse('', Codes::HTTP_OK);
    }

    /**
     * @param ContactRequest $contactRequest
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function update(ContactRequest $contactRequest = null)
    {
        if (!$contactRequest) {
            $contactRequest = new ContactRequest();
        }

        $form = $this->createForm(new ContactRequestType(), $contactRequest);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            /** @var EntityManager $em */
            $em = $this->get('doctrine.orm.entity_manager');

            $contactRequest = $form->getData();
            $em->persist($contactRequest);
            $em->flush();


            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.contact_request.entity.saved')
            );

            return $this->get('oro_ui.router')->actionRedirect(
                [
                    'route' => 'orocrm_contact_request_update',
                    'parameters' => ['id' => $contactRequest->getId()],
                ],
                [
                    'route' => 'orocrm_contact_request_index'
                ]
            );

        }

        return [
            'entity' => $contactRequest,
            'form' => $form->createView(),
        ];
    }
} 
