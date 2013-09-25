<?php

namespace OroCRM\Bundle\SalesBundle\Controller;

use Doctrine\Common\Inflector\Inflector;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Datagrid\OpportunityDatagridManager;

/**
 * @Route("/opportunity")
 */
class OpportunityController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_sales_opportunity_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_sales_opportunity_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMSalesBundle:Opportunity"
     * )
     */
    public function viewAction(Opportunity $entity)
    {
        return array(
            'entity' => $entity,
        );
    }

    /**
     * @Route("/info/{id}", name="orocrm_sales_opportunity_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_sales_opportunity_view")
     */
    public function infoAction(Opportunity $entity)
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
            $entity
        );

        $dynamicRow = array();
        foreach ($fields as $field) {
            $label = $entityProvider->getConfigById($field->getId())->get('label') ? : $field->getId()->getFieldName();

            $dynamicRow[$label] = $entity->{'get' . ucfirst(Inflector::camelize($field->getId()->getFieldName()))}();
        }

        return array(
            'dynamic' => $dynamicRow,
            'entity'  => $entity
        );
    }

    /**
     * @Route("/create", name="orocrm_sales_opportunity_create")
     * @Template("OroCRMSalesBundle:Opportunity:update.html.twig")
     * @Acl(
     *      id="orocrm_sales_opportunity_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMSalesBundle:Opportunity"
     * )
     */
    public function createAction()
    {
        $entity        = new Opportunity();
        $defaultStatus = $this->getDoctrine()->getManager()->find('OroCRMSalesBundle:OpportunityStatus', 'in_progress');
        $entity->setStatus($defaultStatus);

        return $this->update($entity);
    }

    /**
     * @Route("/update/{id}", name="orocrm_sales_opportunity_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_sales_opportunity_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMSalesBundle:Opportunity"
     * )
     */
    public function updateAction(Opportunity $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_sales_opportunity_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("orocrm_sales_opportunity_view")
     */
    public function indexAction()
    {
        /** @var OpportunityDatagridManager $datagridManager */
        $datagridManager = $this->get('orocrm_sales.opportunity.datagrid_manager');
        $datagridView    = $datagridManager->getDatagrid()->createView();

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
     * @param Opportunity $entity
     * @return array
     */
    protected function update(Opportunity $entity)
    {
        if ($this->get('orocrm_sales.opportunity.form.handler')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.sales.controller.opportunity.saved.message')
            );

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route'      => 'orocrm_sales_opportunity_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route'      => 'orocrm_sales_opportunity_view',
                    'parameters' => array('id' => $entity->getId()),
                )
            );
        }

        return array(
            'entity' => $entity,
            'form'   => $this->get('orocrm_sales.opportunity.form')->createView(),
        );
    }
}
