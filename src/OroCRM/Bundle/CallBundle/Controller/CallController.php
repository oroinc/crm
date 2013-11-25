<?php

namespace OroCRM\Bundle\CallBundle\Controller;

use OroCRM\Bundle\CallBundle\Entity\Call;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class CallController extends Controller
{
    /**
     * @Route("/create/{contactId}", name="orocrm_call_create", requirements={"id"="\d+"}, defaults={"contactId"=0})
     * @Template("OroCRMCallBundle:Call:update.html.twig")
     * @Acl(
     *      id="orocrm_call_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMCallBundle:Call"
     * )
     */
    public function createForContactAction($contactId)
    {
        $redirect = ($this->getRequest()->get('noredir')) ? false : true;

        $entity = $this->initEntity($contactId);
        return $this->update($entity, $redirect);
    }

    /**
     * @Route("/update/{id}", name="orocrm_call_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_call_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMCallBundle:Call"
     * )
     */
    public function updateAction(Call $entity = null)
    {
        return $this->update($entity);
    }

    /**
     * @Route("/delete/{id}", name="orocrm_call_delete", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_call_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMCallBundle:Call"
     * )
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $call = $em->getRepository('OroCRMCallBundle:Call')->find($id);

        if ($call) {

            $em->remove($call);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('Call deleted successfully')
            );
            return $this->redirect($this->generateUrl('orocrm_call_index'));
        } else {
            throw new NotFoundHttpException(sprintf('Call with ID %s is not found', $id));
        }
    }

    /**
     * @Route(name="orocrm_call_index")
     * @Template
     * @Acl(
     *      id="orocrm_call_index",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMCallBundle:Call"
     * )
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

   /**
     * @param int $contactId
     * @return Call
     */
    protected function initEntity($contactId = null)
    {
        $entity = $this->getManager()->createEntity();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $entity->setOwner($user);

        $callStatus = $this->getDoctrine()
                           ->getRepository('OroCRMCallBundle:CallStatus')
                           ->findOneByStatus('completed');
        $entity->setCallStatus($callStatus);

        $callDirection = $this->getDoctrine()
                           ->getRepository('OroCRMCallBundle:CallDirection')
                           ->findOneByDirection('outgoing');
        $entity->setDirection($callDirection);
        
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

        return $entity;
    }

    /**
     * @param Call $entity
     * @param bool $redirect
     * @return array
     */
    protected function update(Call $entity = null, $redirect = true)
    {
        $saved = false;

        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }

        if ($this->get('orocrm_call.call.form.handler')->process($entity)) {
            if ($redirect) {
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Call logged successfully')
                );
                return $this->redirect($this->generateUrl('orocrm_call_index'));
            }
            $saved = true;
        }

        return array(
            'saved' => $saved,
            'form' => $this->get('orocrm_call.call.form')->createView()
        );
    }

    public function getManager()
    {
        return $this->get('orocrm_call.call.manager.api');
    }    
}
