<?php

namespace OroCRM\Bundle\CallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\CallBundle\Entity\Call;

class CallController extends Controller
{
    /**
     * @Route("/create", name="orocrm_call_create")
     * @Template("OroCRMCallBundle:Call:update.html.twig")
     * @Acl(
     *      id="orocrm_call_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMCallBundle:Call"
     * )
     */
    public function createAction()
    {
        $redirect = ($this->getRequest()->get('no_redirect')) ? false : true;
        $contactId = $this->getRequest()->get('contactId');
        $accountId = $this->getRequest()->get('accountId');

        $entity = $this->initEntity($contactId, $accountId);
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
    public function updateAction(Call $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(name="orocrm_call_index")
     * @Template
     * @Acl(
     *      id="orocrm_call_view",
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
     * @AclAncestor("orocrm_call_view")
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
     * @Route("/widget/info/{id}", name="orocrm_call_widget_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_call_view")
     */
    public function infoAction(Call $entity)
    {
        return array('entity' => $entity);
    }

    /**
     * @param int|null $contactId
     * @param int|null $accountId
     * @return Call
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function initEntity($contactId = null, $accountId = null)
    {
        $entity = new Call();

        $callStatus = $this->getDoctrine()
            ->getRepository('OroCRMCallBundle:CallStatus')
            ->findOneByName('completed');
        $entity->setCallStatus($callStatus);

        $callDirection = $this->getDoctrine()
            ->getRepository('OroCRMCallBundle:CallDirection')
            ->findOneByName('outgoing');
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

        if ($accountId) {
            $repository = $this->getDoctrine()->getRepository('OroCRMAccountBundle:Account');
            /** @var Account $account */
            $account = $repository->find($accountId);
            if ($account) {
                $entity->setRelatedAccount($account);
            } else {
                throw new NotFoundHttpException(sprintf('Account with ID %s is not found', $accountId));
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
            $entity = new Call();
        }

        if ($this->get('orocrm_call.call.form.handler')->process($entity)) {
            if ($redirect) {
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('orocrm.call.controller.call.saved.message')
                );

                return $this->get('oro_ui.router')->redirectAfterSave(
                    ['route' => 'orocrm_call_update', 'parameters' => ['id' => $entity->getId()]],
                    ['route' => 'orocrm_call_index'],
                    $entity
                );
            }
            $saved = true;
        }

        return array(
            'entity' => $entity,
            'saved' => $saved,
            'form' => $this->get('orocrm_call.call.form')->createView()
        );
    }
}
