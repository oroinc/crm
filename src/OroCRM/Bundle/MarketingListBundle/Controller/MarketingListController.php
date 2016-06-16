<?php

namespace OroCRM\Bundle\MarketingListBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;

/**
 * @Route("/marketing-list")
 */
class MarketingListController extends Controller
{
    /**
     * @Route("/", name="orocrm_marketing_list_index")
     * @AclAncestor("orocrm_marketing_list_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orocrm_marketing_list.entity.class')
        ];
    }

    /**
     * @Route("/view/{id}", name="orocrm_marketing_list_view", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Acl(
     *      id="orocrm_marketing_list_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMMarketingListBundle:MarketingList"
     * )
     * @Template
     *
     * @param MarketingList $entity
     *
     * @return array
     */
    public function viewAction(MarketingList $entity)
    {
        $entityConfig = $this->get('orocrm_marketing_list.entity_provider')->getEntity($entity->getEntity());

        return [
            'entity'   => $entity,
            'config'   => $entityConfig,
            'gridName' => ConfigurationProvider::GRID_PREFIX . $entity->getId()
        ];
    }

    /**
     * @Route("/create", name="orocrm_marketing_list_create")
     * @Template("OroCRMMarketingListBundle:MarketingList:update.html.twig")
     * @Acl(
     *      id="orocrm_marketing_list_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMMarketingListBundle:MarketingList"
     * )
     */
    public function createAction()
    {
        return $this->update(new MarketingList());
    }

    /**
     * @Route("/update/{id}", name="orocrm_marketing_list_update", requirements={"id"="\d+"}, defaults={"id"=0})
     *
     * @Template
     * @Acl(
     *      id="orocrm_marketing_list_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMMarketingListBundle:MarketingList"
     * )
     *
     * @param MarketingList $entity
     *
     * @return array
     */
    public function updateAction(MarketingList $entity)
    {
        return $this->update($entity);
    }

    /**
     * @param MarketingList $entity
     *
     * @return array
     */
    protected function update(MarketingList $entity)
    {
        $response = $this->get('oro_form.model.update_handler')->update(
            $entity,
            $this->get('orocrm_marketing_list.form.marketing_list'),
            $this->get('translator')->trans('orocrm.marketinglist.entity.saved'),
            $this->get('orocrm_marketing_list.form.handler.marketing_list')
        );

        if (is_array($response)) {
            return array_merge(
                $response,
                [
                    'entities' => $this->get('oro_entity.entity_provider')->getEntities(),
                    'metadata' => $this->get('oro_query_designer.query_designer.manager')->getMetadata('segment')
                ]
            );
        }

        return $response;
    }
}
