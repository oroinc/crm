<?php
namespace OroCRM\Bundle\AccountBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

class TickerSymbolType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_account_ticker_symbol';
    }
}
