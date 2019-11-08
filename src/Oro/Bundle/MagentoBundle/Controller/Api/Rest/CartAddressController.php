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
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\CartAddress;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * API CRUD controller for CartAddress entity.
 *
 * @NamePrefix("oro_api_")
 */
class CartAddressController extends RestController implements ClassResourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_magento.cart_address.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_magento.form.cart_address.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('oro_magento.form.handler.cart_address');
    }

    /**
     * Add shipping address to the cart.
     *
     * @Post(requirements={"cartId"="\d+"})
     *
     * @ApiDoc(
     *      description="Add shipping address to the cart",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_magento_cart_address_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroMagentoBundle:CartAddress"
     * )
     * @param int $cartId
     *
     * @return JsonResponse
     */
    public function postShippingAction(int $cartId)
    {
        return $this->post($cartId, AddressType::TYPE_SHIPPING);
    }

    /**
     * Add billing address to the cart.
     *
     * @Post(requirements={"cartId"="\d+"})
     *
     * @ApiDoc(
     *      description="Add billing address to the cart",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_magento_cart_address_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroMagentoBundle:CartAddress"
     * )
     * @param int $cartId
     *
     * @return JsonResponse
     */
    public function postBillingAction(int $cartId)
    {
        return $this->post($cartId, AddressType::TYPE_BILLING);
    }

    /**
     * Get address by type.
     *
     * @param int $cartId
     *
     * @Get(requirements={"cartId"="\d+"})
     *
     * @ApiDoc(
     *      description="Get address by type",
     *      resource=true
     * )
     * @AclAncestor("oro_magento_cart_view")
     *
     * @return Response
     */
    public function getShippingAction(int $cartId)
    {
        return $this->getAddress($cartId, AddressType::TYPE_SHIPPING);
    }

    /**
     * Get address item by type.
     *
     * @param int $cartId
     *
     * @Get(requirements={"cartId"="\d+"})
     *
     * @ApiDoc(
     *      description="Get address item by type",
     *      resource=true
     * )
     * @AclAncestor("oro_magento_cart_view")
     *
     * @return Response
     */
    public function getBillingAction(int $cartId)
    {
        return $this->getAddress($cartId, AddressType::TYPE_BILLING);
    }

    /**
     * Update cart shipping address.
     *
     * @param int $cartId cart id
     *
     * @Put(requirements={"cartId"="\d+"})
     *
     * @ApiDoc(
     *      description="Update cart shipping address",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_magento_cart_address_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroMagentoBundle:CartAddress"
     * )
     * @return Response
     */
    public function putShippingAction(int $cartId)
    {
        return $this->put($cartId, AddressType::TYPE_SHIPPING);
    }

    /**
     * Update cart billing address.
     *
     * @param int $cartId cart id
     *
     * @Put(requirements={"cartId"="\d+"})
     *
     * @ApiDoc(
     *      description="Update cart billing address",
     *      resource=true
     * )
     * @AclAncestor("oro_magento_cart_address_update")
     *
     * @return Response
     */
    public function putBillingAction(int $cartId)
    {
        return $this->put($cartId, AddressType::TYPE_BILLING);
    }

    /**
     * Delete cart shipping address.
     *
     * @param int $cartId
     *
     * @Delete(requirements={"cartId"="\d+"})
     *
     * @ApiDoc(
     *      description="Delete cart shipping address",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_magento_cart_address_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroMagentoBundle:CartAddress"
     * )
     * @return Response
     */
    public function deleteShippingAction(int $cartId)
    {
        return $this->delete($cartId, AddressType::TYPE_SHIPPING);
    }

    /**
     * Delete cart billing address.
     *
     * @param int $cartId
     *
     * @Delete(requirements={"cartId"="\d+"})
     *
     * @ApiDoc(
     *      description="Delete cart billing address",
     *      resource=true
     * )
     * @AclAncestor("oro_magento_cart_address_delete")
     * @return Response
     */
    public function deleteBillingAction(int $cartId)
    {
        return $this->delete($cartId, AddressType::TYPE_BILLING);
    }

    /**
     * @param int    $cartId
     * @param string $type
     *
     * @return Response
     */
    protected function delete($cartId, $type)
    {
        $cart        = $this->getCartManager()->findOneBy(['id' => $cartId]);
        $isProcessed = false;
        $address     = null;
        $addressId   = null;

        if ($cart) {
            $address   = $this->getManager()->getAddress($cart, $type);
            $addressId = $address->getId();
            if (!$address) {
                $view = $this->view(null, Response::HTTP_NOT_FOUND);
            } else {
                try {
                    $this->getDeleteHandler()->handleDelete($addressId, $this->getManager());
                    $isProcessed = true;
                    $view        = $this->view(null, Response::HTTP_NO_CONTENT);
                } catch (EntityNotFoundException $e) {
                    $view = $this->view(null, Response::HTTP_NOT_FOUND);
                } catch (AccessDeniedException $e) {
                    $view = $this->view(['reason' => $e->getMessage()], Response::HTTP_FORBIDDEN);
                }
            }
        } else {
            $view = $this->view(null, Response::HTTP_NO_CONTENT);
        }

        return $this->buildResponse($view, self::ACTION_DELETE, ['id' => $addressId, 'success' => $isProcessed]);
    }

    /**
     * @param int    $cartId
     * @param string $type
     *
     * @return Response
     */
    protected function put($cartId, $type)
    {
        /** @var CartAddress $address */
        $cart      = $this->getCartManager()->findOneBy(['id' => $cartId]);
        $address   = null;
        $addressId = null;

        if (!empty($cart)) {
            $address   = $this->getManager()->getAddress($cart, $type);
            $addressId = $address->getId();
            if ($address) {
                if ($this->processForm($address)) {
                    $view = $this->view(null, Response::HTTP_NO_CONTENT);
                } else {
                    $view = $this->view($this->getForm(), Response::HTTP_BAD_REQUEST);
                }
            } else {
                $view = $this->view(null, Response::HTTP_NOT_FOUND);
            }
        } else {
            $view = $this->view(null, Response::HTTP_NO_CONTENT);
        }

        return $this->buildResponse($view, self::ACTION_UPDATE, ['id' => $addressId, 'entity' => $address]);
    }

    /**
     * @param CartAddress $entity
     * @param Cart        $cart
     * @param string      $type
     *
     * @return bool
     */
    protected function processAddressForm(CartAddress $entity, Cart $cart, $type)
    {
        $this->fixRequestAttributes($entity);

        return $this->getFormHandler()->process($entity, $cart, $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCartManager()
    {
        return $this->get('oro_magento.cart.manager.api');
    }

    /**
     * @param int    $cartId
     * @param string $type
     *
     * @return JsonResponse
     */
    protected function getAddress($cartId, $type)
    {
        $cart    = $this->getCartManager()->findOneBy(['id' => $cartId]);
        $address = null;

        if ($cart) {
            $address = $this->getManager()->getSerializedAddress($cart, $type);
        }

        return new JsonResponse(
            $address,
            empty($address) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * @param int    $cartId
     * @param string $type
     *
     * @return Response
     */
    protected function post($cartId, $type)
    {
        /** @var Cart $cart */
        $cart        = $this->getCartManager()->find($cartId);
        $isProcessed = false;
        $entity      = new CartAddress();

        if (!empty($cart)) {
            $isProcessed = $this->processAddressForm($entity, $cart, $type);

            if (true === $isProcessed) {
                $view = $this->view($this->createResponseData($entity), Response::HTTP_CREATED);
            } else {
                $view = $this->view($this->getForm(), Response::HTTP_BAD_REQUEST);
            }
        } else {
            $view = $this->view($this->getForm(), Response::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($view, self::ACTION_CREATE, ['success' => $isProcessed, 'entity' => $entity]);
    }
}
