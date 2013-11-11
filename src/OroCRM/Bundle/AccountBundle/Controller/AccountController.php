<?php

namespace OroCRM\Bundle\AccountBundle\Controller;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Util\Inflector;

use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Query;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

use OroCRM\Bundle\AccountBundle\Entity\Account;

class AccountController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_account_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_account_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMAccountBundle:Account"
     * )
     */
    public function viewAction(Account $account)
    {
        return [
            'entity'  => $account,
            'dynamic' => $this->getDynamicFields($account)
        ];
    }

    /**
     * Create account form
     *
     * @Route("/create", name="orocrm_account_create")
     * @Template("OroCRMAccountBundle:Account:update.html.twig")
     * @Acl(
     *      id="orocrm_account_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMAccountBundle:Account"
     * )
     */
    public function createAction()
    {
        return $this->update();
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="orocrm_account_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_account_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMAccountBundle:Account"
     * )
     */
    public function updateAction(Account $entity = null)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_account_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @AclAncestor("orocrm_account_view")
     * @Template
     */
    public function indexAction()
    {
        return [];
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
        return $this->get('orocrm_account.account.manager.api');
    }

    /**
     * @param Account $entity
     * @return array
     */
    protected function update(Account $entity = null)
    {
        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }

        if ($this->get('orocrm_account.form.handler.account')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.account.controller.account.saved.message')
            );

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'orocrm_account_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route' => 'orocrm_account_view',
                    'parameters' => array('id' => $entity->getId())
                )
            );
        }

        return array(
            'form'     => $this->get('orocrm_account.form.account')->createView()
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * TODO: will be refactored via twig extension
     */
    protected function getDynamicFields(Account $entity)
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
            get_class($entity)
        );

        $dynamicRow = array();

        foreach ($fields as $field) {
            $fieldName = $field->getId()->getFieldName();
            $value = $entity->{'get' . ucfirst(Inflector::camelize($fieldName))}();

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
                       = $value;
        }

        return $dynamicRow;
    }

    /**
     * @Route(
     *      "/contact/select/{id}",
     *      name="orocrm_account_contact_select",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @Template
     * @AclAncestor("orocrm_contact_view")
     */
    public function contactDatagridAction(Account $entity = null)
    {
        return [
            'account' => $entity ? $entity->getId() : $entity
        ];
    }
}
