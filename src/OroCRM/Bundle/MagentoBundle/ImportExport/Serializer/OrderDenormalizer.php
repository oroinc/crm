<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\ImportExportBundle\Field\FieldHelper;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Service\ImportHelper;
use OroCRM\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class OrderDenormalizer extends ConfigurableEntityNormalizer
{
    /** @var ImportHelper */
    protected $importHelper;

    /** @var ChannelHelper */
    protected $channelImportHelper;

    /**
     * @param FieldHelper   $fieldHelper
     * @param ImportHelper  $importHelper
     * @param ChannelHelper $channelHelper
     */
    public function __construct(FieldHelper $fieldHelper, ImportHelper $importHelper, ChannelHelper $channelHelper)
    {
        parent::__construct($fieldHelper);
        $this->importHelper        = $importHelper;
        $this->channelImportHelper = $channelHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof Order;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return $type == MagentoConnectorInterface::ORDER_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (array_key_exists('paymentDetails', $data)) {
            $data['paymentDetails'] = $this->importHelper->denormalizePaymentDetails($data['paymentDetails']);
        }

        /** @var Order $order */
        $order = parent::denormalize($data, $class, $format, $context);

        $integration = $this->importHelper->getIntegrationFromContext($context);
        $order->setChannel($integration);
        $order->setDataChannel($this->channelImportHelper->getChannel($integration));

        if ($order->getStore()) {
            $order->getStore()->setChannel($integration);
            $order->getStore()->getWebsite()->setChannel($integration);
        }

        return $order;
    }
}
