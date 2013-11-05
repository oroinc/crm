<?php

namespace OroCRM\Bundle\ContactBundle\Controller;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\PersistentCollection;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\AccountBundle\Entity\Account;

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
        return [
            'entity' => $contact,
        ];
    }

    /**
     * @Route("/info/{id}", name="orocrm_contact_info", requirements={"id"="\d+"})
     *
     * @Template
     * @AclAncestor("orocrm_contact_view")
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * TODO: will be refactored via twig extension
     */
    public function infoAction(Contact $contact)
    {
        /** @var \Oro\Bundle\EntityConfigBundle\Config\ConfigManager $configManager */
        $configManager  = $this->get('oro_entity_config.config_manager');
        $extendProvider = $this->get('oro_entity_config.provider.extend');
        $entityProvider = $this->get('oro_entity_config.provider.entity');
        $viewProvider   = $this->get('oro_entity_config.provider.view');

        $fields = $extendProvider->filter(
            function (ConfigInterface $config) use ($viewProvider, $extendProvider) {
                $extendConfig = $extendProvider->getConfigById($config->getId());

                return
                    $config->is('owner', ExtendManager::OWNER_CUSTOM)
                    && !$config->is('state', ExtendManager::STATE_NEW)
                    && !$config->is('is_deleted')
                    && $viewProvider->getConfigById($config->getId())->is('is_displayable')
                    && !(
                        in_array($extendConfig->getId()->getFieldType(), array('oneToMany', 'manyToOne', 'manyToMany'))
                        && $extendProvider->getConfig($extendConfig->get('target_entity'))->is('is_deleted', true)
                    );
            },
            get_class($contact)
        );

        $dynamicRow = array();

        foreach ($fields as $field) {
            $fieldName = $field->getId()->getFieldName();
            $value     = $contact->{'get' . ucfirst(Inflector::camelize($fieldName))}();

            /** Prepare DateTime field type */
            if ($value instanceof \DateTime) {
                $configFormat = $this->get('oro_config.global')->get('oro_locale.date_format') ? : 'Y-m-d';
                $value        = $value->format($configFormat);
            }

            /** Prepare Relation field type */
            if ($value instanceof PersistentCollection) {
                $collection     = $value;
                $extendConfig   = $extendProvider->getConfigById($field->getId());
                $titleFieldName = $extendConfig->get('target_title');

                /** generate link for related entities collection */
                $route       = false;
                $routeParams = false;

                if (class_exists($extendConfig->get('target_entity'))) {
                    /** @var EntityMetadata $metadata */
                    $metadata = $configManager->getEntityMetadata($extendConfig->get('target_entity'));
                    if ($metadata && $metadata->routeView) {
                        $route       = $metadata->routeView;
                        $routeParams = array(
                            'id' => null
                        );
                    }

                    $relationExtendConfig = $extendProvider->getConfig($extendConfig->get('target_entity'));
                    if ($relationExtendConfig->is('owner', ExtendManager::OWNER_CUSTOM)) {
                        $route       = 'oro_entity_view';
                        $routeParams = array(
                            'entity_id' => str_replace('\\', '_', $extendConfig->get('target_entity')),
                            'id'        => null
                        );
                    }
                }

                $value = array(
                    'route'        => $route,
                    'route_params' => $routeParams,
                    'values'       => array()
                );

                foreach ($collection as $item) {
                    $routeParams['id'] = $item->getId();

                    $title = [];
                    foreach ($titleFieldName as $fieldName) {
                        $title[] = $item->{Inflector::camelize('get_' . $fieldName)}();
                    }

                    $value['values'][] = array(
                        'id'    => $item->getId(),
                        'link'  => $route ? $this->generateUrl($route, $routeParams) : false,
                        'title' => implode(' ', $title)
                    );
                }
            }

            $fieldName = $field->getId()->getFieldName();
            $dynamicRow[$entityProvider->getConfigById($field->getId())->get('label') ? : $fieldName]
                       = $value; //$contact->{'get' . ucfirst(Inflector::camelize($fieldName))}();
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
        return [];
    }

    /**
     * @Route(
     *      "/{contactId}/email-create",
     *      name="orocrm_contact_email_create",
     *      requirements={"contactId"="\d+"}
     * )
     * @AclAncestor("oro_email_create")
     * @ParamConverter("contact", options={"id" = "contactId"})
     */
    public function createEmailAction(Contact $contact)
    {
        $query = $this->getRequest()->query->all();
        if ($contact->getPrimaryEmail()) {
            $query['to'] = $contact->getPrimaryEmail()->getEmail();
        }
        $query['gridName'] = 'contact_emails';

        return $this->forward(
            'OroEmailBundle:Email:create',
            array(),
            $query
        );
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
            'entity' => $entity,
            'form'   => $this->get('orocrm_contact.form.contact')->createView(),
        );
    }
}
