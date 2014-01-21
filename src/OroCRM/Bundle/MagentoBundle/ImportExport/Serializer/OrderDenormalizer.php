<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class OrderDenormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == MagentoConnectorInterface::ORDER_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $channel = $this->getChannelFromContext($context);

        $website = $data['store']['website'];
        $website = $this->serializer->denormalize($website, MagentoConnectorInterface::WEBSITE_TYPE);
        $website->setChannel($channel);

        $data['store'] = $this->denormalizeObject($data, 'store', MagentoConnectorInterface::STORE_TYPE);
        $data['store']->setWebsite($website);
        $data['store']->setChannel($channel);

        $data                   = $this->denormalizeCreatedUpdated($data, $format);
        $data['paymentDetails'] = $this->denormalizePaymentDetails($data['paymentDetails']);
        $data['addresses']      = $this
            ->denormalizeObject($data, 'addresses', MagentoConnectorInterface::ORDER_ADDRESS_COLLECTION_TYPE);

        $data['items'] = $this
            ->denormalizeObject($data, 'items', MagentoConnectorInterface::ORDER_ITEM_COLLECTION_TYPE);

        /** @var Order $order */
        $className = MagentoConnectorInterface::ORDER_TYPE;
        $order     = new $className();
        $this->fillResultObject($order, $data);

        $order->setChannel($channel);

        return $order;
    }
}
