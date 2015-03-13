<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\ImportExportBundle\Field\FieldHelper;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Service\ImportHelper;
use OroCRM\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\Service\StateManager;

class OrderDenormalizer extends ConfigurableEntityNormalizer
{
    /** @var ImportHelper */
    protected $importHelper;

    /** @var ChannelHelper */
    protected $channelImportHelper;

    /**
     * @var StateManager
     */
    protected $stateManager;

    /**
     * @param FieldHelper   $fieldHelper
     * @param ImportHelper  $importHelper
     * @param ChannelHelper $channelHelper
     */
    public function __construct(FieldHelper $fieldHelper, ImportHelper $importHelper, ChannelHelper $channelHelper)
    {
        parent::__construct($fieldHelper);

        $this->importHelper = $importHelper;
        $this->channelImportHelper = $channelHelper;
        $this->stateManager = new StateManager();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $data instanceof Order;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return $type == MagentoConnectorInterface::ORDER_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (array_key_exists('paymentDetails', $data)) {
            $data['paymentDetails'] = $this->importHelper->denormalizePaymentDetails($data['paymentDetails']);
        }

        if (array_key_exists('items', $data)) {
            $items = $data['items'];
            $item = reset($items);
            if (!is_array($item)) {
                $data['items'] = [$items];
            }
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
