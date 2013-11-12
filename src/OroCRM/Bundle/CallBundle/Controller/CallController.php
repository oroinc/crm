<?php

namespace OroCRM\Bundle\CallBundle\Controller;

use OroCRM\Bundle\CallBundle\Entity\Call;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class CallController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_call_view", requirements={"id"="\d+"})
     * @Template
     */
    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $call = $em->getRepository('OroCRMCallBundle:Call')->find($id);

        return array('call' => $call);
    }

    /**
     * @Route("/create/{contactId}", name="orocrm_call_create", requirements={"id"="\d+"}, defaults={"contactId"=0})
     * @Template("OroCRMCallBundle:Call:update.html.twig")
     */
    public function createForContactAction($contactId)
    {
        return $this->update(null, $contactId);
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="orocrm_call_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function updateAction(Call $entity = null)
    {
        return $this->update($entity);
    }

    /**
     * @param Call $entity
     * @return array
     */
    protected function update(Call $entity = null, $contactId = 0)
    {
        $responseData['saved'] = false;

        if (!$entity) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            $entity = new Call();
            $entity->setOwner($user);

            $callStatus = $this->getDoctrine()->getRepository('OroCRMCallBundle:CallStatus')->findOneByStatus('completed');
            $entity->setCallStatus($callStatus);

            $contact = null;
            if ($contactId == 0) {
                $contactId = $this->getRequest()->get('contactId');
            }
            if ($contactId) {
                $repository = $this->getDoctrine()->getRepository('OroCRMContactBundle:Contact');
                $contact = $repository->find($contactId);
                if ($contact) {                
                    $entity->setRelatedContact($contact);
                    $entity->setContactPhoneNumber($contact->getPrimaryPhone());
                } else {
                    throw new NotFoundHttpException(sprintf('Contact with ID %s is not found', $contactId));
                }
            }        
        }

        if ($this->get('orocrm_call.call.form.handler')->process($entity)) {
            $responseData['saved'] = true;
            /*
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.call.controller.call.saved.message')
            );

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'orocrm_call_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route' => 'orocrm_call_view',
                    'parameters' => array('id' => $entity->getId()),
                )
            );
            */
        }

        $responseData['form'] = $this->get('orocrm_call.call.form')->createView();
        return $responseData;
    }    
}