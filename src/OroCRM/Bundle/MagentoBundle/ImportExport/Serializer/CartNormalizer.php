<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class CartNormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == MagentoConnectorInterface::CART_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $channel    = $this->getChannelFromContext($context);
        $serializer = $this->serializer;

        $data['cartItems'] = $serializer->denormalize($data['cartItems'], MagentoConnectorInterface::CART_ITEMS_TYPE);

        $data['customer'] = $this->denormalizeCustomer($data, $context);

        $website = $serializer->denormalize($data['store']['website'], MagentoConnectorInterface::WEBSITE_TYPE);
        $website->setChannel($channel);

        $data['store'] = $serializer->denormalize($data['store'], MagentoConnectorInterface::STORE_TYPE);
        $data['store']->setWebsite($website);
        $data['store']->setChannel($channel);

        $data = $this->denormalizeCreatedUpdated($data, $format, $context);

        $data['shippingAddress'] = $this->serializer->denormalize(
            $data['shipping_address'],
            MagentoConnectorInterface::CART_ADDRESS_TYPE
        );
        $data['billingAddress']  = $this->serializer->denormalize(
            $data['billing_address'],
            MagentoConnectorInterface::CART_ADDRESS_TYPE
        );

        $data['paymentDetails'] = $this->denormalizePaymentDetails($data['paymentDetails']);

        $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : true;
        $data['status'] = $this->denormalizeStatus($isActive);

        $cartClass = MagentoConnectorInterface::CART_TYPE;
        /** @var Cart $cart */
        $cart = new $cartClass();
        $this->fillResultObject($cart, $data);

        $cart->setChannel($channel);

        return $cart;
    }

    /**
     * @param $data
     * @param $context
     *
     * @return Customer
     */
    protected function denormalizeCustomer($data, $context)
    {
        $group = $this->serializer->denormalize(
            $data['customer']['group'],
            MagentoConnectorInterface::CUSTOMER_GROUPS_TYPE
        );
        $group->setChannel($this->getChannelFromContext($context));

        $customerClass = MagentoConnectorInterface::CUSTOMER_TYPE;
        /** @var Customer $customer */
        $customer = new $customerClass();
        $this->fillResultObject($customer, $data['customer']);

        if (!empty($data['email'])) {
            $customer->setEmail($data['email']);
        }

        return $customer;
    }

    /**
     * Denormalize status based on isActive field
     *
     * @param bool $isActive
     */
    protected function denormalizeStatus($isActive)
    {
        $statusClass =  MagentoConnectorInterface::CART_STATUS_TYPE;
        $status = new $statusClass($isActive ? 'open' : 'expired');

        return $status;
    }
}
