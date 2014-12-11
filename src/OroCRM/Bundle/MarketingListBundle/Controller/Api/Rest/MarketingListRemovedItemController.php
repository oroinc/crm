<?php

namespace OroCRM\Bundle\MarketingListBundle\Controller\Api\Rest;

use Doctrine\ORM\EntityNotFoundException;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListRemovedItem;

/**
 * @Rest\RouteResource("marketinglist_removeditem")
 * @Rest\NamePrefix("orocrm_api_")
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
     * Remove marketing list entity item from list
     *
     * Returns
     * - HTTP_OK (200)
     *
     * @Rest\Get(
     *      "/marketinglist/{marketingList}/remove/{id}"
     * )
     * @ApiDoc(description="Remove marketing list entity item", resource=true)
     * @AclAncestor("orocrm_marketing_list_removed_item_delete")
     *
     * @param MarketingList $marketingList
     * @param int           $id
     *
     * @return Response
     */
    public function removeAction(MarketingList $marketingList, $id)
    {
        $item = new MarketingListRemovedItem();
        $item
            ->setMarketingList($marketingList)
            ->setEntityId($id);

        $violations = $this->get('validator')->validate($item);
        if ($violations->count()) {
            return $this->handleView($this->view($violations, Codes::HTTP_BAD_REQUEST));
        }

        $em = $this->getManager()->getObjectManager();
        $em->persist($item);
        $em->flush($item);

        $entityName = $this
            ->get('oro_entity_config.provider.entity')
            ->getConfig($marketingList->getEntity())
            ->get('label');

        return $this->handleView(
            $this->view(
                array(
                    'successful' => true,
                    'message'    => $this->get('translator')->trans(
                        'orocrm.marketinglist.controller.removed',
                        ['%entityName%' => $this->get('translator')->trans($entityName)]
                    )
                ),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * @param MarketingList $marketingList
     * @param int           $id
     *
     * @Rest\Get(
     *      "/marketinglist/{marketingList}/unremove/{id}"
     * )
     * @ApiDoc(
     *     description="Delete MarketingListRemovedItem by marketing list entity",
     *     resource=true
     * )
     * @AclAncestor("orocrm_marketing_list_removed_item_delete")
     *
     * @return Response
     */
    public function unremoveAction(MarketingList $marketingList, $id)
    {
        /** @var MarketingListRemovedItem[] $forRemove */
        $forRemove = $this->getManager()->getRepository()->findBy(
            array(
                'marketingList' => $marketingList,
                'entityId'      => $id
            )
        );

        if ($forRemove) {
            try {
                $item = $forRemove[0];
                $this->getDeleteHandler()->handleDelete($item->getId(), $this->getManager());
            } catch (EntityNotFoundException $notFoundEx) {
                return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
            } catch (ForbiddenException $forbiddenEx) {
                return $this->handleView(
                    $this->view(['reason' => $forbiddenEx->getReason()], Codes::HTTP_FORBIDDEN)
                );
            }
        }

        $entityName = $this
            ->get('oro_entity_config.provider.entity')
            ->getConfig($marketingList->getEntity())
            ->get('label');

        return $this->handleView(
            $this->view(
                array(
                    'successful' => true,
                    'message'    => $this->get('translator')->trans(
                        'orocrm.marketinglist.controller.unremoved',
                        ['%entityName%' => $this->get('translator')->trans($entityName)]
                    )
                ),
                Codes::HTTP_OK
            )
        );
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
