<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\ImportExportBundle\Field\FieldHelper;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\Service\ImportHelper;

class OrderDenormalizer extends ConfigurableEntityNormalizer
{
    /**
     * @var ImportHelper
     */
    protected $importHelper;

    /**
     * @param FieldHelper $fieldHelper
     * @param ImportHelper $contextHelper
     */
    public function __construct(FieldHelper $fieldHelper, ImportHelper $contextHelper)
    {
        parent::__construct($fieldHelper);
        $this->importHelper = $contextHelper;
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
        if (!empty($data['addresses'])) {
            foreach ($data['addresses'] as $idx => $address) {
                $data['addresses'][$idx] = $this->importHelper->getFixedAddress($address);
            }
        }

        /** @var Order $order */
        $order = parent::denormalize($data, $class, $format, $context);

        $channel = $this->importHelper->getChannelFromContext($context);
        $order->setChannel($channel);
        if ($order->getStore()) {
            $order->getStore()->setChannel($channel);
        }

        return $order;
    }
}
