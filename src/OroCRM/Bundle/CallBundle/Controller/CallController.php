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
     * @Route("/view/{id}", name="orocrm_call_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_call_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMCallBundle:Call"
     * )     
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
     * @Acl(
     *      id="orocrm_call_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMCallBundle:Call"
     * )
     */
    public function createForContactAjaxAction()
    {
        return $this->update(null, 0, false);
    }

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
        return $this->update(null, $contactId, true);
    }

    /**
     * @Route("/update/{id}", name="orocrm_call_update", requirements={"id"="\d+"}, defaults={"id"=0})
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
                        'route'      => 'orocrm_call_index'
                    ),
                    array(
                        'route'      => 'orocrm_call_index'
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
     * @Route("/delete/{id}", name="orocrm_call_delete", requirements={"id"="\d+"}, defaults={"id"=0})
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
            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route'      => 'orocrm_call_index'
                ),
                array(
                    'route'      => 'orocrm_call_index'
                )
            );
        } else {
            throw new NotFoundHttpException(sprintf('Call with ID %s is not found', $contactId));
        }
    }

    /**
     * @Route(name="orocrm_call_index")
     * @Template
     * @AclAncestor("orocrm_call_view")
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
