<?php

namespace Oro\Bundle\MagentoBundle\Twig;

use Oro\Bundle\MagentoBundle\Entity\IntegrationAwareInterface;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Entity\Order;

class OrderNotesExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'oro_magento_order_notes';

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('oro_magento_order_notes_is_applicable', [$this, 'isOrderNotesApplicable']),
        ];
    }

    /**
     * Checks if notes grid is allowed to be shown
     *
     * @param $entity
     * @return bool
     */
    public function isOrderNotesApplicable($entity)
    {
        if ($entity instanceof IntegrationAwareInterface) {
            /** @var MagentoTransport $magentoTransport */
            $magentoTransport = $entity->getChannel()->getTransport();
            $isSupportedOrderNoteFunctionality = $magentoTransport->isSupportedOrderNoteExtensionVersion();
            $isDisplayOnAccountOrCustomerPage = $magentoTransport->getIsDisplayOrderNotes();
            if ($isSupportedOrderNoteFunctionality && ($isDisplayOnAccountOrCustomerPage || $entity instanceof Order)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }
}
