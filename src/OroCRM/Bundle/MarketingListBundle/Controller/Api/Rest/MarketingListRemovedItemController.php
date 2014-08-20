<?php

namespace OroCRM\Bundle\MarketingListBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @RouteResource("marketinglist_removeditem")
 * @NamePrefix("orocrm_api_")
 */
class MarketingListRemovedItemController extends RestController implements ClassResourceInterface
{
    /**
     * REST POST
     *
     * @ApiDoc(
     *     description="Create new MarketingListRemovedItem",
     *     resource=true
     * )
     * @AclAncestor("orocrm_marketing_list_removed_item_create")
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *     description="Delete MarketingListRemovedItem",
     *     resource=true
     * )
     * @AclAncestor("orocrm_marketing_list_removed_item_delete")
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_marketing_list.marketing_list_removed_item.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('orocrm_marketing_list.form.marketing_list_removed_item');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_marketing_list.form.handler.marketing_list_removed_item');
    }
}
