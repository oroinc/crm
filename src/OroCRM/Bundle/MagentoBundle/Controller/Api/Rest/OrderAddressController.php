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
use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;

/**
 * @NamePrefix("oro_api_")
 */
class OrderAddressController extends RestController implements ClassResourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_magento.order_address.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('orocrm_magento.form.order_address.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_magento.form.handler.order_address.api');
    }

    /**
     * Get all addresses items.
     *
     * @ApiDoc(
     *      description="Get all addresses items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_order_view")
     * @param int $orderId
     *
     * @return JsonResponse
     */
    public function cgetAction($orderId)
    {
        $addressItems = $this->getManager()->getAllSerializedItems($orderId);

        return new JsonResponse(
            $addressItems,
            empty($addressItems) ? Codes::HTTP_NOT_FOUND : Codes::HTTP_OK
        );
    }

    /**
     * Add address to the order.
     *
     * @ApiDoc(
     *      description="Add address to the order",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_order_address_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMMagentoBundle:OrderAddress"
     * )
     * @param int $orderId
     *
     * @return JsonResponse
     */
    public function postAction($orderId)
    {
        /** @var Order $order */
        $order       = $this->getOrderManager()->find($orderId);
        $isProcessed = false;
        $entity      = new OrderAddress();

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
     * Get order address.
     *
     * @param int $addressId
     * @param int $orderId
     *
     * @ApiDoc(
     *      description="Get order address",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_order_view")
     *
     * @return Response
     */
    public function getAction($orderId, $addressId)
    {
        $address = $this->getManager()->serializeElement($orderId, $addressId);

        return new JsonResponse(
            $address,
            empty($address) ? Codes::HTTP_NOT_FOUND : Codes::HTTP_OK
        );
    }

    /**
     * Update order address.
     *
     * @param int $addressId order address item id
     * @param int $orderId   order id
     *
     * @ApiDoc(
     *      description="Update order address",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_order_address_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMMagentoBundle:OrderAddress"
     * )
     * @return Response
     */
    public function putAction($orderId, $addressId)
    {
        /** @var OrderAddress $address */
        $address = $this->getManager()->findOneBy(['owner' => $orderId, 'id' => $addressId]);

        if ($address) {
            if ($this->processForm($address)) {
                $view = $this->view(null, Codes::HTTP_NO_CONTENT);
            } else {
                $view = $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);
            }
        } else {
            $view = $this->view(null, Codes::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($view, self::ACTION_UPDATE, ['id' => $addressId, 'entity' => $address]);
    }

    /**
     * Delete order address.
     *
     * @param int $addressId order address item id
     * @param int $orderId   order id
     *
     * @ApiDoc(
     *      description="Delete order address",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_order_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMMagentoBundle:OrderAddress"
     * )
     * @return Response
     */
    public function deleteAction($orderId, $addressId)
    {
        $isProcessed = false;

        /** @var OrderAddress $address */
        $address = $this->getManager()->findOneBy(['owner' => $orderId, 'id' => $addressId]);

        if (!$address) {
            $view = $this->view(null, Codes::HTTP_NOT_FOUND);
        } else {
            try {
                $this->getDeleteHandler()->handleDelete($addressId, $this->getManager());
                $isProcessed = true;
                $view        = $this->view(null, Codes::HTTP_NO_CONTENT);
            } catch (EntityNotFoundException $notFoundEx) {
                $view = $this->view(null, Codes::HTTP_NOT_FOUND);
            } catch (ForbiddenException $forbiddenEx) {
                $view = $this->view(['reason' => $forbiddenEx->getReason()], Codes::HTTP_FORBIDDEN);
            }
        }

        return $this->buildResponse($view, self::ACTION_DELETE, ['id' => $addressId, 'success' => $isProcessed]);
    }

    /**
     * @return OrderApiEntityManager
     */
    protected function getOrderManager()
    {
        return $this->get('orocrm_magento.order.manager.api');
    }
}
