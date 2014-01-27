<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\AddressDataConverter;

class CartNormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    /** @var AddressDataConverter */
    protected $addressConverter;

    public function __construct(EntityManager $em, AddressDataConverter $addressConverter)
    {
        parent::__construct($em);
        $this->addressConverter = $addressConverter;
    }

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

        $data['shippingAddress'] = $this->denormalizeAddress($data, 'shipping');
        $data['billingAddress']  = $this->denormalizeAddress($data, 'billing');

        $data['paymentDetails'] = $this->denormalizePaymentDetails($data['paymentDetails']);

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
     * @param array  $data
     * @param string $type shipping or billing
     *
     * @return CartAddress
     */
    protected function denormalizeAddress($data, $type)
    {
        $key  = $type . '_address';
        $data = $this->addressConverter->convertToImportFormat($data[$key]);

        if (empty($data['country'])) {
            return null;
        } else {
            return $this->serializer
                ->denormalize($data, MagentoConnectorInterface::CART_ADDRESS_TYPE)
                ->setOriginId($data['originId']);
        }
    }
}
