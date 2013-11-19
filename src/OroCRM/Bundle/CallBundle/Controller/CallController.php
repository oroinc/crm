<?php

namespace OroCRM\Bundle\CallBundle\Controller;

use OroCRM\Bundle\CallBundle\Entity\Call;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @Route("/create/ajax", name="orocrm_call_create_ajax")
     * @Template("OroCRMCallBundle:Call:update.html.twig")
     */
    public function createForContactAjaxAction()
    {
        return $this->update(null, 0, false);
    }

    /**
     * @Route("/create/{contactId}", name="orocrm_call_create", requirements={"id"="\d+"}, defaults={"contactId"=0})
     * @Template("OroCRMCallBundle:Call:update.html.twig")
     */
    public function createForContactAction($contactId)
    {
        return $this->update(null, $contactId, true);
    }

    /**
     * @Route("/update/{id}", name="orocrm_call_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function updateAction(Call $entity = null)
    {
        return $this->update($entity, null, true);
    }

    /**
     * @param Call $entity
     * @return array
     */
    protected function update(Call $entity = null, $contactId = 0, $redirect = false)
    {
        $saved = false;

        if (!$entity) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            $entity = new Call();
            $entity->setOwner($user);

            $callStatus = $this->getDoctrine()
                               ->getRepository('OroCRMCallBundle:CallStatus')
                               ->findOneByStatus('completed');

            $callDirection = $this->getDoctrine()
                               ->getRepository('OroCRMCallBundle:CallDirection')
                               ->findOneByDirection('outgoing');

            $entity->setCallStatus($callStatus);
            $entity->setDirection($callDirection);
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

        if ($this->get('orocrm_call.call.form.handler')->process($entity)) {
            if ($redirect) {
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Call logged successfully')
                );
                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route'      => 'orocrm_contact_view',
                        'parameters' => array('id' => $entity->getRelatedContact()->getId())
                    ),
                    array(
                        'route'      => 'orocrm_contact_view',
                        'parameters' => array('id' => $entity->getRelatedContact()->getId())
                    )
                );
            }
            $saved = true;
        }

        return array(
            'saved' => $saved,
            'form' => $this->get('orocrm_call.call.form')->createView()
        );
    }

    /**
     * @Route(name="orocrm_call_index")
     * @Template
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/widget", name="orocrm_call_widget_calls")
     * @Template
     *
     * @param Request $request
     * @return array
     */
    public function callsAction(Request $request)
    {
        return array(
            'datagridParameters' => $request->query->all()
        );
    }
}
