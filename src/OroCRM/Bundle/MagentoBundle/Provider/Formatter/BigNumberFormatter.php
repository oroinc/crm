<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;

class BigNumberFormatter
{
    /** @var NumberFormatter */
    protected $numberFormatter;

    /**
     * @param NumberFormatter $numberFormatter
     */
    public function __construct(NumberFormatter $numberFormatter)
    {
        $this->numberFormatter = $numberFormatter;
    }

    /**
     * @param mixed  $value
     * @param string $type
     * @param bool   $isDeviant
     *
     * @return string
     */
    public function formatValue($value, $type = '', $isDeviant = false)
    {
        $sign = null;

        if ($isDeviant && $value !== 0) {
            $sign  = $value > 0 ? '+' : '&minus;';
            $value = abs($value);
        }
        switch ($type) {
            case 'currency':
                $value = $this->numberFormatter->formatCurrency($value);
                break;
            case 'percent':
                if ($isDeviant) {
                    $value = round(($value) * 100, 0) / 100;
                } else {
                    $value = round(($value) * 100, 2) / 100;
                }

                $value = $this->numberFormatter->formatPercent($value);
                break;
            default:
                $value = $this->numberFormatter->formatDecimal($value);
        }

        return $isDeviant && !is_null($sign) ? sprintf('%s%s', $sign, $value) : $value;
    }
}
