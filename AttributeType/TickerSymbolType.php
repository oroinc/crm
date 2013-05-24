<?php
namespace Oro\Bundle\AccountBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

class TickerSymbolType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_account_ticker_symbol';
    }
}
