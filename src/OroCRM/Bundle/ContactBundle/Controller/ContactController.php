<?php

namespace OroCRM\Bundle\ContactBundle\Controller;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Datagrid\ContactDatagridManager;
use OroCRM\Bundle\ContactBundle\Datagrid\ContactAccountUpdateDatagridManager;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Datagrid\ContactEmailDatagridManager;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class ContactController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_contact_view", requirements={"id"="\d+"})
     *
     * @Template
     * @Acl(
     *      id="orocrm_contact_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMContactBundle:Contact"
     * )
     */
    public function viewAction(Contact $contact)
    {
        /** @var ContactEmailDatagridManager $manager */
        $manager = $this->get('orocrm_contact.email.datagrid_manager');
        $manager->setContact($contact);
        $datagridView = $manager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array(
            'entity'   => $contact,
            'datagrid' => $datagridView,
        );
    }

    /**
     * @Route("/info/{id}", name="orocrm_contact_info", requirements={"id"="\d+"})
     *
     * @Template
     * @AclAncestor("orocrm_contact_view")
     */
    public function infoAction(Contact $contact)
    {
        $extendProvider = $this->get('oro_entity_config.provider.extend');
        $entityProvider = $this->get('oro_entity_config.provider.entity');
        $viewProvider   = $this->get('oro_entity_config.provider.view');

        $fields = $extendProvider->filter(
            function (ConfigInterface $config) use ($viewProvider) {
                return
                    $config->is('owner', ExtendManager::OWNER_CUSTOM)
                    && !$config->is('state', ExtendManager::STATE_NEW)
                    && !$config->is('is_deleted')
                    && $viewProvider->getConfigById($config->getId())->is('is_displayable');
            },
            get_class($contact)
        );

        $dynamicRow = array();
        foreach ($fields as $field) {
            $label = $entityProvider->getConfigById($field->getId())->get('label') ? : $field->getId()->getFieldName();

            $dynamicRow[$label] = $contact->{'get' . ucfirst(Inflector::camelize($field->getId()->getFieldName()))}();
        }

        return array(
            'dynamic' => $dynamicRow,
            'entity'  => $contact
        );
    }

    /**
     * Create contact form
     * @Route("/create", name="orocrm_contact_create")
     * @Template("OroCRMContactBundle:Contact:update.html.twig")
     * @Acl(
     *      id="orocrm_contact_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMContactBundle:Contact"
     * )
     */
    public function createAction()
    {
        // add predefined account to contact
        $contact   = null;
        $accountId = $this->getRequest()->get('account');
        if ($accountId) {
            $repository = $this->getDoctrine()->getRepository('OroCRMAccountBundle:Account');
            /** @var Account $account */
            $account = $repository->find($accountId);
            if ($account) {
                /** @var Contact $contact */
                $contact = $this->getManager()->createEntity();
                $contact->addAccount($account);
            } else {
                throw new NotFoundHttpException(sprintf('Account with ID %s is not found', $accountId));
            }
        }

        return $this->update($contact);
    }

    /**
     * Update user form
     * @Route("/update/{id}", name="orocrm_contact_update", requirements={"id"="\d+"}, defaults={"id"=0})
     *
     * @Template
     * @Acl(
     *      id="orocrm_contact_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMContactBundle:Contact"
     * )
     */
    public function updateAction(Contact $entity = null)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_contact_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     *
     * @Template
     * @AclAncestor("orocrm_contact_view")
     */
    public function indexAction()
    {
        /** @var $gridManager ContactDatagridManager */
        $gridManager  = $this->get('orocrm_contact.contact.datagrid_manager');
        $datagridView = $gridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array('datagrid' => $datagridView);
    }

    /**
     * @return FlashBag
     */
    protected function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }

    /**
     * @return ApiEntityManager
     */
    protected function getManager()
    {
        return $this->get('orocrm_contact.contact.manager');
    }

    protected function update(Contact $entity = null)
    {
        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }

        /** @var $accountDatagridManager ContactAccountUpdateDatagridManager */
        $accountDatagridManager = $this->get('orocrm_contact.account.update_datagrid_manager');
        $accountDatagridManager->setContact($entity);
        $datagridView = $accountDatagridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        if ($this->get('orocrm_contact.form.handler.contact')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.contact.controller.contact.saved.message')
            );

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route'      => 'orocrm_contact_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route'      => 'orocrm_contact_view',
                    'parameters' => array('id' => $entity->getId())
                )
            );
        }

        return array(
            'entity'   => $entity,
            'form'     => $this->get('orocrm_contact.form.contact')->createView(),
            'datagrid' => $datagridView,
        );
    }
}
