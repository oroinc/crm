<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;
use OroCRM\Bundle\MagentoBundle\Provider\StoreConnector;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\OrderAddressDataConverter;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\AddressDataConverter;

class OrderNormalizer extends AbstractNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const ORDER_TYPE   = 'OroCRM\Bundle\MagentoBundle\Entity\Order';
    const ADDRESS_TYPE = 'OroCRM\Bundle\MagentoBundle\Entity\OrderAddress';

    /** @var OrderAddressDataConverter */
    protected $addressDataConverter;

    public function __construct(OrderAddressDataConverter $addressDataConverter)
    {
        $this->addressDataConverter = $addressDataConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Order;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == self::ORDER_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        // will be implemented for bidirectional sync
        throw new \Exception('Normalization is not implemented!');
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $channel = $context['channel'];
        $data    = is_array($data) ? $data : [];

        $website = $this->serializer
            ->denormalize($data['store']['website'], StoreConnector::WEBSITE_TYPE, $format, $context);
        $website->setChannel($channel);


        $data['store'] = $this->serializer->denormalize(
            $data['store'],
            StoreConnector::STORE_TYPE,
            $format,
            $context
        );
        $data['store']->setWebsite($website);
        $data['store']->setChannel($channel);

        $data = $this->denormalizeCreatedUpdated($data, $format, $context);

        $data['shippingAddress'] = $this->denormalizeAddress($data, 'shipping', $format, $context);
        $data['billingAddress']  = $this->denormalizeAddress($data, 'billing', $format, $context);
        // todo  and store and items and address denormalization

        $order = new Order();
        $this->fillResultObject($order, $data);

        $order->setChannel($channel);

        return $order;
    }


    /**
     * @param array  $data
     * @param string $type shipping or billing
     * @param string $format
     * @param array  $context
     *
     * @return OrderAddress
     */
    protected function denormalizeAddress($data, $type, $format, $context)
    {
        $key  = $type . '_address';
        $data = $this->addressDataConverter->convertToImportFormat($data[$key]);

        return $this->serializer
            ->denormalize($data, self::ADDRESS_TYPE, $format, $context);
    }
}
