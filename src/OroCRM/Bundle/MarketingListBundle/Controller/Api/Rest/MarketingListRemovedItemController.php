<?php

namespace OroCRM\Bundle\MarketingListBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListRemovedItem;
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
     * @param MarketingList $marketingList
     * @param int $id
     *
     * @ApiDoc(
     *     description="Delete MarketingListRemovedItem by marketing list entity",
     *     resource=true
     * )
     * @AclAncestor("orocrm_marketing_list_removed_item_delete")
     *
     * @return Response
     */
    public function deleteByEntityAction(MarketingList $marketingList, $id)
    {
        /** @var MarketingListRemovedItem[] $forRemove */
        $forRemove = $this->getManager()->getRepository()->findBy(
            array(
                'marketingList' => $marketingList,
                'entityId' => $id
            )
        );
        if ($forRemove) {
            $item = $forRemove[0];
            return $this->handleDeleteRequest($item->getId());
        } else {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }
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
