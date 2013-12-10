<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\AddressDataConverter;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Oro\Bundle\AddressBundle\Entity\Address;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Provider\StoreConnector;

class CartNormalizer extends AbstractNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const ADDRESS_TYPE = 'OroCRM\Bundle\MagentoBundle\Entity\Address';

    /** @var AddressDataConverter */
    protected $addressConverter;

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Cart;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == 'OroCRM\Bundle\MagentoBundle\Entity\Cart';
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (method_exists($object, 'toArray')) {
            $result = $object->toArray($format, $context);
        } else {
            $result = array(
                'id'          => $object->getId(),
                'customer_id' => $object->getCustomer() ? $object->getCustomer()->getId() : null,
                'email'       => $object->getEmail(),
                'store'       => $object->getStore() ? $object->getStore()->getCode() : null,
                'origin_id'   => $object->getOriginId(),
                'items_qty'   => $object->getItemsQty(),
                'grand_total' => $object->getGrandTotal(),
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $channel = $context['channel'];
        $serializer = $this->serializer;
        $data         = is_array($data) ? $data : [];

        $data['cartItems'] = $serializer->denormalize(
            $data['cartItems'],
            CartItemNormalizer::ENTITIES_TYPE,
            $format,
            $context
        );

        $data['customer'] = $this->denormalizeCustomer($data, $format, $context);

        $website = $serializer->denormalize($data['store']['website'], StoreConnector::WEBSITE_TYPE, $format, $context);
        $website->setChannel($channel);

        $data['store'] = $serializer->denormalize(
            $data['store'],
            StoreConnector::STORE_TYPE,
            $format,
            $context
        );
        $data['store']->setWebsite($website);
        $data['store']->setChannel($channel);

        $data = $this->denormalizeCreatedUpdated($data, $format, $context);

        $data['shipping_address'] = $this->denormalizeAddress($data, 'shipping', $format, $context);
        $data['billing_address'] = $this->denormalizeAddress($data, 'billing', $format, $context);

        $cart = new Cart();
        $this->fillResultObject($cart, $data);

        $cart->setChannel($channel);

        return $cart;
    }

    /**
     * @param $data
     * @param $format
     * @param $context
     *
     * @return Customer
     */
    protected function denormalizeCustomer($data, $format, $context)
    {
        $group = $this->serializer->denormalize(
            $data['customer']['group'],
            CustomerNormalizer::GROUPS_TYPE,
            $format,
            $context
        );
        $group->setChannel($context['channel']);

        $customer = new Customer();
        $this->fillResultObject($customer, $data['customer']);

        if (!empty($data['email'])) {
            $customer->setEmail($data['email']);
        }

        return $customer;
    }

    /**
     * @param array  $data
     * @param string $type shipping or billing
     * @param string $format
     * @param array  $context
     *
     * @return Address
     */
    protected function denormalizeAddress($data, $type, $format, $context)
    {
        $key = $type . '_address';
        $data = $this->addressConverter->convertToImportFormat($data[$key]);

        return $this->serializer->denormalize($data, self::ADDRESS_TYPE, $format, $context);
    }

    /**
     * @param AddressDataConverter $addressConverter
     */
    public function setAddressDataConverter(AddressDataConverter $addressConverter)
    {
        $this->addressConverter = $addressConverter;
    }
}
