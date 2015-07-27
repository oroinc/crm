<?php

namespace OroCRM\Bundle\MagentoBundle\Controller\Api\Rest;

use Doctrine\ORM\EntityNotFoundException;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\Manager\OrderApiEntityManager;
use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;

/**
 * @NamePrefix("oro_api_")
 */
class OrderItemController extends RestController implements ClassResourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_magento.order_item.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('orocrm_magento.form.order_item.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_magento.form.handler.order_item');
    }

    /**
     * Add item to the order.
     *
     * @ApiDoc(
     *      description="Add item to the order",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_order_item_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMMagentoBundle:OrderItem"
     * )
     * @param int $orderId
     *
     * @return Response
     */
    public function postAction($orderId)
    {
        /** @var Order $order */
        $order       = $this->getOrderManager()->find($orderId);
        $isProcessed = false;
        $entity      = new OrderItem();

        if (!empty($order)) {
            $entity = $this->processForm($entity);

            if ($entity) {
                $view = $this->view($this->createResponseData($entity), Codes::HTTP_CREATED);
                $isProcessed = true;
            } else {
                $view = $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);
            }
        } else {
            $view = $this->view($this->getForm(), Codes::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($view, self::ACTION_CREATE, ['success' => $isProcessed, 'entity' => $entity]);
    }

    /**
     * Get order item.
     *
     * @param int $orderId
     * @param int $itemId
     *
     * @ApiDoc(
     *      description="Get order item",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_order_view")
     *
     * @return Response
     */
    public function getAction($orderId, $itemId)
    {
        $orderItem = $this->getManager()->getSpecificSerializedItem($orderId, $itemId);

        return new JsonResponse(
            $orderItem,
            empty($orderItem) ? Codes::HTTP_NOT_FOUND : Codes::HTTP_OK
        );
    }

    /**
     * Get all order items.
     *
     * @ApiDoc(
     *      description="Get all order items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_order_view")
     *
     * @param int $orderId
     *
     * @return JsonResponse
     */
    public function cgetAction($orderId)
    {
        $orderItems = $this->getManager()->getAllSerializedItems($orderId);

        return new JsonResponse(
            $orderItems,
            empty($orderItems) ? Codes::HTTP_NOT_FOUND : Codes::HTTP_OK
        );
    }

    /**
     * Update order item.
     *
     * @param int $itemId  order item id
     * @param int $orderId order id
     *
     * @ApiDoc(
     *      description="Update order item",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_order_item_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMMagentoBundle:OrderItem"
     * )
     * @return Response
     */
    public function putAction($orderId, $itemId)
    {
        $orderItem = $this->getManager()->findOneBy(['order' => $orderId, 'id' => $itemId]);

        if ($orderItem) {
            if ($this->processForm($orderItem)) {
                $view = $this->view(null, Codes::HTTP_NO_CONTENT);
            } else {
                $view = $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);
            }
        } else {
            $view = $this->view(null, Codes::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($view, self::ACTION_UPDATE, ['id' => $itemId, 'entity' => $orderItem]);
    }

    /**
     * Delete order item.
     *
     * @param int $itemId  item id
     * @param int $orderId order id
     *
     * @ApiDoc(
     *      description="Delete order item",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_order_item_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMMagentoBundle:OrderItem"
     * )
     * @return Response
     */
    public function deleteAction($orderId, $itemId)
    {
        $isProcessed = false;

        $orderItem = $this->getManager()->findOneBy(['order' => $orderId, 'id' => $itemId]);

        if (!$orderItem) {
            $view = $this->view(null, Codes::HTTP_NOT_FOUND);
        } else {
            try {
                $this->getDeleteHandler()->handleDelete($itemId, $this->getManager());

                $isProcessed = true;
                $view        = $this->view(null, Codes::HTTP_NO_CONTENT);
            } catch (EntityNotFoundException $notFoundEx) {
                $view = $this->view(null, Codes::HTTP_NOT_FOUND);
            } catch (ForbiddenException $forbiddenEx) {
                $view = $this->view(['reason' => $forbiddenEx->getReason()], Codes::HTTP_FORBIDDEN);
            }
        }

        return $this->buildResponse($view, self::ACTION_DELETE, ['id' => $itemId, 'success' => $isProcessed]);
    }

    /**
     * @return OrderApiEntityManager
     */
    protected function getOrderManager()
    {
        return $this->get('orocrm_magento.order.manager.api');
    }
}
