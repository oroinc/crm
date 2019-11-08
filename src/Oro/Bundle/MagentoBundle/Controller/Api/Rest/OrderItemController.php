<?php

namespace Oro\Bundle\MagentoBundle\Controller\Api\Rest;

use Doctrine\ORM\EntityNotFoundException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\MagentoBundle\Entity\Manager\OrderApiEntityManager;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderItem;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * API CRUD controller for OrderItem entity.
 *
 * @NamePrefix("oro_api_")
 */
class OrderItemController extends RestController implements ClassResourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_magento.order_item.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_magento.form.order_item.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('oro_magento.form.handler.order_item');
    }

    /**
     * Add item to the order.
     *
     * @Post(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Add item to the order",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_magento_order_item_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroMagentoBundle:OrderItem"
     * )
     * @param int $orderId
     *
     * @return Response
     */
    public function postAction(int $orderId)
    {
        /** @var Order $order */
        $order       = $this->getOrderManager()->find($orderId);
        $isProcessed = false;
        $entity      = new OrderItem();

        if (!empty($order)) {
            $entity = $this->processForm($entity);

            if ($entity) {
                $view = $this->view($this->createResponseData($entity), Response::HTTP_CREATED);
                $isProcessed = true;
            } else {
                $view = $this->view($this->getForm(), Response::HTTP_BAD_REQUEST);
            }
        } else {
            $view = $this->view($this->getForm(), Response::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($view, self::ACTION_CREATE, ['success' => $isProcessed, 'entity' => $entity]);
    }

    /**
     * Get order item.
     *
     * @param int $orderId
     * @param int $itemId
     *
     * @Get(requirements={"orderId"="\d+", "itemId"="\d+"})
     *
     * @ApiDoc(
     *      description="Get order item",
     *      resource=true
     * )
     * @AclAncestor("oro_magento_order_view")
     *
     * @return Response
     */
    public function getAction(int $orderId, int $itemId)
    {
        $orderItem = $this->getManager()->getSpecificSerializedItem($orderId, $itemId);

        return new JsonResponse(
            $orderItem,
            empty($orderItem) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * Get all order items.
     *
     * @Get(requirements={"orderId"="\d+"})
     *
     * @ApiDoc(
     *      description="Get all order items",
     *      resource=true
     * )
     * @AclAncestor("oro_magento_order_view")
     *
     * @param int $orderId
     *
     * @return JsonResponse
     */
    public function cgetAction(int $orderId)
    {
        $orderItems = $this->getManager()->getAllSerializedItems($orderId);

        return new JsonResponse(
            $orderItems,
            empty($orderItems) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * Update order item.
     *
     * @param int $itemId  order item id
     * @param int $orderId order id
     *
     * @Put(requirements={"orderId"="\d+", "itemId"="\d+"})
     *
     * @ApiDoc(
     *      description="Update order item",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_magento_order_item_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroMagentoBundle:OrderItem"
     * )
     * @return Response
     */
    public function putAction(int $orderId, int $itemId)
    {
        $orderItem = $this->getManager()->findOneBy(['order' => $orderId, 'id' => $itemId]);

        if ($orderItem) {
            if ($this->processForm($orderItem)) {
                $view = $this->view(null, Response::HTTP_NO_CONTENT);
            } else {
                $view = $this->view($this->getForm(), Response::HTTP_BAD_REQUEST);
            }
        } else {
            $view = $this->view(null, Response::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($view, self::ACTION_UPDATE, ['id' => $itemId, 'entity' => $orderItem]);
    }

    /**
     * Delete order item.
     *
     * @param int $itemId  item id
     * @param int $orderId order id
     *
     * @Delete(requirements={"orderId"="\d+", "itemId"="\d+"})
     *
     * @ApiDoc(
     *      description="Delete order item",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_magento_order_item_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroMagentoBundle:OrderItem"
     * )
     * @return Response
     */
    public function deleteAction(int $orderId, int $itemId)
    {
        $isProcessed = false;

        $orderItem = $this->getManager()->findOneBy(['order' => $orderId, 'id' => $itemId]);

        if (!$orderItem) {
            $view = $this->view(null, Response::HTTP_NOT_FOUND);
        } else {
            try {
                $this->getDeleteHandler()->handleDelete($itemId, $this->getManager());

                $isProcessed = true;
                $view        = $this->view(null, Response::HTTP_NO_CONTENT);
            } catch (EntityNotFoundException $e) {
                $view = $this->view(null, Response::HTTP_NOT_FOUND);
            } catch (AccessDeniedException $e) {
                $view = $this->view(['reason' => $e->getMessage()], Response::HTTP_FORBIDDEN);
            }
        }

        return $this->buildResponse($view, self::ACTION_DELETE, ['id' => $itemId, 'success' => $isProcessed]);
    }

    /**
     * @return OrderApiEntityManager
     */
    protected function getOrderManager()
    {
        return $this->get('oro_magento.order.manager.api');
    }
}
