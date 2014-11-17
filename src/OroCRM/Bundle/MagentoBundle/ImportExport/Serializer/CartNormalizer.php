<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\ImportExportBundle\Field\FieldHelper;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Service\ImportHelper;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;

class CartNormalizer extends ConfigurableEntityNormalizer
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
        return $data instanceof Cart;
    }

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
        if (array_key_exists('paymentDetails', $data)) {
            $data['paymentDetails'] = $this->importHelper->denormalizePaymentDetails($data['paymentDetails']);
        }

        /** @var Cart $cart */
        $cart = parent::denormalize($data, $class, $format, $context);

        $integration = $this->importHelper->getIntegrationFromContext($context);
        $cart->setChannel($integration);
        $cart->setDataChannel($this->channelImportHelper->getChannel($integration));
        if ($cart->getStore()) {
            $cart->getStore()->setChannel($integration);
            $cart->getStore()->getWebsite()->setChannel($integration);
        }

        if (!empty($data['email'])) {
            $cart->getCustomer()->setEmail($data['email']);
        }

        $this->updateStatus($cart, $data);

        return $cart;
    }

    /**
     * @param Cart  $cart
     * @param array $data
     */
    protected function updateStatus(Cart $cart, array $data)
    {
        $statusClass = MagentoConnectorInterface::CART_STATUS_TYPE;
        $isActive    = isset($data['is_active']) ? (bool)$data['is_active'] : true;
        $cart->setStatus(new $statusClass($isActive ? 'open' : 'expired'));
    }
}
