<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class CartNormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return $type == MagentoConnectorInterface::CART_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $cartClass = MagentoConnectorInterface::CART_TYPE;
        /** @var Cart $cart */
        $cart = new $cartClass();
        if (!is_array($data)) {
            return $cart;
        }
        $channel    = $this->getChannelFromContext($context);
        $serializer = $this->serializer;

        $this->setCartItems($cart, $data, $format, $context);
        $this->setCustomer($cart, $data, $format, $context);

        $website = $serializer->denormalize(
            $data['store']['website'],
            MagentoConnectorInterface::WEBSITE_TYPE,
            $format,
            $context
        );
        if ($website) {
            $website->setChannel($channel);
        }

        $data['store'] = $serializer->denormalize(
            $data['store'],
            MagentoConnectorInterface::STORE_TYPE,
            $format,
            $context
        );
        $data['store']->setWebsite($website);
        $data['store']->setChannel($channel);

        $data = $this->denormalizeCreatedUpdated($data, $format, $context);

        $data['shippingAddress'] = $this->serializer->denormalize(
            $data['shipping_address'],
            MagentoConnectorInterface::CART_ADDRESS_TYPE,
            $format,
            $context
        );
        $data['billingAddress']  = $this->serializer->denormalize(
            $data['billing_address'],
            MagentoConnectorInterface::CART_ADDRESS_TYPE,
            $format,
            $context
        );

        $data['paymentDetails'] = $this->denormalizePaymentDetails($data['paymentDetails']);

        $isActive       = isset($data['is_active']) ? (bool)$data['is_active'] : true;
        $data['status'] = $this->denormalizeStatus($isActive);

        $this->fillResultObject($cart, $data);
        $cart->setChannel($channel);

        return $cart;
    }

    protected function setCartItems(Cart $object, $data, $format, $context)
    {
        $cartItems = $this->denormalizeObject(
            $data,
            'cartItems',
            MagentoConnectorInterface::CART_ITEMS_TYPE,
            $format,
            $context
        );
        if (!empty($cartItems)) {
            $object->setCartItems($cartItems);
        }
    }

    /**
     * @param Cart $cart
     * @param array $data
     * @param string $format
     * @param array $context
     */
    protected function setCustomer(Cart $cart, $data, $format, $context)
    {
        $customer = $this->serializer->denormalize(
            $data['customer'],
            MagentoConnectorInterface::CUSTOMER_TYPE,
            $format,
            $context
        );
        if (!empty($data['email'])) {
            $customer->setEmail($data['email']);
        }
        if ($customer) {
            $cart->setCustomer($customer);
        }
    }

    /**
     * Denormalize status based on isActive field
     *
     * @param bool $isActive
     */
    protected function denormalizeStatus($isActive)
    {
        $statusClass = MagentoConnectorInterface::CART_STATUS_TYPE;
        $status      = new $statusClass($isActive ? 'open' : 'expired');

        return $status;
    }
}
