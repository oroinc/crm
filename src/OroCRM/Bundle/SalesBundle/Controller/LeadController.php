<?php

namespace OroCRM\Bundle\SalesBundle\Controller;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Util\ClassUtils;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Datagrid\LeadDatagridManager;

/**
 * @Route("/lead")
 */
class LeadController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_sales_lead_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_sales_lead_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMSalesBundle:Lead"
     * )
     */
    public function viewAction(Lead $lead)
    {
        return array(
            'entity' => $lead
        );
    }

    /**
     * @Route("/info/{id}", name="orocrm_sales_lead_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_sales_lead_view")
     */
    public function infoAction(Lead $lead)
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
            $lead
        );

        $dynamicRow = array();
        foreach ($fields as $field) {
            $label = $entityProvider->getConfigById($field->getId())->get('label') ? : $field->getId()->getFieldName();

            $dynamicRow[$label] = $lead->{'get' . ucfirst(Inflector::camelize($field->getId()->getFieldName()))}();
        }

        return array(
            'dynamic' => $dynamicRow,
            'entity'  => $lead
        );
    }

    /**
     * @Route("/address-book/{id}", name="orocrm_sales_lead_address_book", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_sales_lead_view")
     */
    public function addressBookAction(Lead $lead)
    {
        return array(
            'entity' => $lead
        );
    }

    /**
     * Create lead form
     * @Route("/create", name="orocrm_sales_lead_create")
     * @Template("OroCRMSalesBundle:Lead:update.html.twig")
     * @Acl(
     *      id="orocrm_sales_lead_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMSalesBundle:Lead"
     * )
     */
    public function createAction()
    {
        $lead = new Lead();
        $defaultStatus = $this->getDoctrine()->getManager()->find('OroCRMSalesBundle:LeadStatus', 'new');
        $lead->setStatus($defaultStatus);

        return $this->update($lead);
    }

    /**
     * Create lead and redirect to qualification
     *
     * @Route("/createAndQualify", name="orocrm_sales_lead_create_and_qualify")
     * @AclAncestor("orocrm_sales_lead_create")
     */
    public function createAndQualifyAction()
    {
        // TODO: refactor workflow functionality to allow custom transition buttons on forms,
        // TODO: should be done in scope of https://magecore.atlassian.net/browse/CRM-544
        $lead = new Lead();
        $defaultStatus = $this->getDoctrine()->getManager()->find('OroCRMSalesBundle:LeadStatus', 'new');
        $lead->setStatus($defaultStatus);

        $response = $this->update($lead);

        if (is_object($response) && $response instanceof RedirectResponse) {
            // start workflow using qualify transition
            $jsonResponse = $this->forward(
                'OroWorkflowBundle:Api/Rest/Workflow:start',
                array(
                    'entityClass' => ClassUtils::getClass($lead),
                    'entityId' => $lead->getId(),
                    'workflowName' => 'sales_lead',
                    'transitionName' => 'qualify',
                    '_format' => 'json',
                )
            );

            // throw an exception if forward action wasn't successful
            $responseStatusCode = $jsonResponse->getStatusCode();
            if ($responseStatusCode != 200) {
                throw new HttpException($responseStatusCode, 'Can\'t qualify created lead');
            }

            // redirect to Sales flow
            $jsonResponseData = json_decode($jsonResponse->getContent(), true);
            if (!empty($jsonResponseData['workflowItem']['result']['redirectUrl'])) {
                return $this->redirect($jsonResponseData['workflowItem']['result']['redirectUrl']);
            }
        }

        return $response;
    }

    /**
     * Update user form
     * @Route("/update/{id}", name="orocrm_sales_lead_update", requirements={"id"="\d+"}, defaults={"id"=0})
     *
     * @Template
     * @Acl(
     *      id="orocrm_sales_lead_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMSalesBundle:Lead"
     * )
     */
    public function updateAction(Lead $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_sales_lead_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("orocrm_sales_lead_view")
     */
    public function indexAction()
    {
        /** @var $gridManager LeadDatagridManager */
        $gridManager  = $this->get('orocrm_sales.lead.datagrid_manager');
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
     * @param Lead $entity
     * @return array
     */
    protected function update(Lead $entity)
    {
        if ($this->get('orocrm_sales.lead.form.handler')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.sales.controller.lead.saved.message')
            );

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route'      => 'orocrm_sales_lead_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route'      => 'orocrm_sales_lead_view',
                    'parameters' => array('id' => $entity->getId()),
                )
            );
        }

        return array(
            'entity' => $entity,
            'form'   => $this->get('orocrm_sales.lead.form')->createView()
        );
    }
}
