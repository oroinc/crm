<?php
declare(strict_types=1);

namespace Oro\Bundle\CRMBundle\Migration;

use Oro\Bundle\EntityExtendBundle\Migration\AbstractCleanupMarketingMigrationQuery;

/**
 * Removes entity configs of MagentoBundle entities.
 */
class CleanupMagentoOneConnectorEntityConfigsQuery extends AbstractCleanupMarketingMigrationQuery
{
    /**
     * All MagentoBundle entities
     */
    public const ENTITY_CLASSES = [
        'Oro\Bundle\MagentoBundle\Entity\Address',
        'Oro\Bundle\MagentoBundle\Entity\CartAddress',
        'Oro\Bundle\MagentoBundle\Entity\CartItem',
        'Oro\Bundle\MagentoBundle\Entity\Cart',
        'Oro\Bundle\MagentoBundle\Entity\CartStatus',
        'Oro\Bundle\MagentoBundle\Entity\CreditMemoItem',
        'Oro\Bundle\MagentoBundle\Entity\CreditMemo',
        'Oro\Bundle\MagentoBundle\Entity\CustomerGroup',
        'Oro\Bundle\MagentoBundle\Entity\Customer',
        'Oro\Bundle\MagentoBundle\Entity\MagentoRestTransport',
        'Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport',
        'Oro\Bundle\MagentoBundle\Entity\MagentoTransport',
        'Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber',
        'Oro\Bundle\MagentoBundle\Entity\OrderAddress',
        'Oro\Bundle\MagentoBundle\Entity\OrderItem',
        'Oro\Bundle\MagentoBundle\Entity\OrderNote',
        'Oro\Bundle\MagentoBundle\Entity\Order',
        'Oro\Bundle\MagentoBundle\Entity\Product',
        'Oro\Bundle\MagentoBundle\Entity\Region',
        'Oro\Bundle\MagentoBundle\Entity\Store',
        'Oro\Bundle\MagentoBundle\Entity\Website',
        'Extend\Entity\EV_Creditmemo_Status',
        'Extend\Entity\EV_Mage_Subscr_Status',
    ];

    public function getClassNames()
    {
        return self::ENTITY_CLASSES;
    }
}
