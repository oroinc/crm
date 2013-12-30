<?php
namespace OroCRM\Bundle\MagentoBundle\Grid;

use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class FormatterContextResolver
{
    /**
     * Return currency from given row
     *
     * @return callable
     */
    public static function getResolverCurrencyClosure()
    {
        return function (ResultRecordInterface $record, $value, NumberFormatter $formatter) {
            return [$record->getValue('currency')];
        };
    }
}
