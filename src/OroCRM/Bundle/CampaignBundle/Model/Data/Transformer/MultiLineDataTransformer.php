<?php

namespace OroCRM\Bundle\CampaignBundle\Model\Data\Transformer;

use Oro\Bundle\ChartBundle\Model\Data\ArrayData;
use Oro\Bundle\ChartBundle\Model\Data\DataInterface;
use Oro\Bundle\ChartBundle\Model\Data\MappedData;
use Oro\Bundle\ChartBundle\Model\Data\Transformer\TransformerInterface;

use OroCRM\Bundle\CampaignBundle\Entity\Campaign;

class MultiLineDataTransformer implements TransformerInterface
{
    /**
     * Force daily scale if more than N days where used for hourly period
     */
    const MAX_DAYS = 15;

    /**
     * @var array
     */
    protected $sourceData;

    /**
     * @var array
     */
    protected $chartOptions;

    /**
     * @var string
     */
    protected $labelKey;

    /**
     * @var string
     */
    protected $valueKey;

    /**
     * @var string
     */
    protected $groupingOption;

    /**
     * @var string
     */
    protected $period = null;

    /**
     * @var string
     */
    protected $format = null;

    /**
     * @var array
     */
    protected $periodMap = [
        Campaign::PERIOD_HOURLY  => 'hour',
        Campaign::PERIOD_DAILY   => 'day',
        Campaign::PERIOD_MONTHLY => 'month'
    ];

    /**
     * @var array
     */
    protected $dateFormatMap = [
        Campaign::PERIOD_HOURLY  => 'Y-m-d H:i:s.u',
        Campaign::PERIOD_DAILY   => 'Y-m-d',
        Campaign::PERIOD_MONTHLY => 'Y-m-d'
    ];

    /**
     * @param DataInterface $data
     * @param array         $chartOptions
     *
     * @return DataInterface
     */
    public function transform(DataInterface $data, array $chartOptions)
    {
        $this->initialize($data, $chartOptions);

        if (!$data->toArray()) {
            return new ArrayData([]);
        }

        $labels = $this->getLabels();

        // create default values
        $values = array_fill(0, sizeof($labels), 0);
        $value  = array_combine($labels, $values);

        // set default values
        $values = [];
        foreach ($this->sourceData as $sourceDataValue) {
            $key = $sourceDataValue[$this->groupingOption];

            $values[$key] = $value;
        }

        // set values
        foreach ($this->sourceData as $sourceDataValue) {
            $key   = $sourceDataValue[$this->groupingOption];
            $label = $sourceDataValue[$this->labelKey];
            $value = $sourceDataValue[$this->valueKey];

            unset($values[$key][$label]);

            $values[$key][] = [
                'label' => $label,
                'value' => $value
            ];
        }

        foreach ($values as $valueKey => $value) {
            foreach ($value as $itemKey => $item) {
                if (!is_array($item)) {
                    unset($values[$valueKey][$itemKey]);

                    $values[$valueKey][] = [
                        'label' => $itemKey,
                        'value' => 0
                    ];
                }
            }
        }

        return new ArrayData($values);
    }

    /**
     * @param DataInterface $data
     * @param array         $chartOptions
     */
    protected function initialize(DataInterface $data, array $chartOptions)
    {
        if (empty($chartOptions['default_settings']['groupingOption'])) {
            throw new \InvalidArgumentException(
                'Options "groupingOption" is not set'
            );
        }

        /** @var MappedData $data */
        $this->sourceData     = $data->getSourceData()->toArray();
        $this->labelKey       = $chartOptions['data_schema']['label']['field_name'];
        $this->valueKey       = $chartOptions['data_schema']['value']['field_name'];
        $this->groupingOption = $chartOptions['default_settings']['groupingOption'];

        if (empty($chartOptions['default_settings']['period'])) {
            throw new \InvalidArgumentException(
                'Options "period" is not set'
            );
        }
        $this->period = $chartOptions['default_settings']['period'];
    }

    /**
     * @return array
     */
    protected function getLabels()
    {
        $labels = [];
        foreach ($this->sourceData as $sourceDataValue) {
            $labels[] = $sourceDataValue[$this->labelKey];
        }
        asort($labels);

        $format = $this->dateFormatMap[$this->period];

        $start = \DateTime::createFromFormat($format, reset($labels));
        $end   = \DateTime::createFromFormat($format, end($labels));

        if (!$start || !$end) {
            return array_unique($labels);
        }

        if ($this->period == Campaign::PERIOD_HOURLY && $end->diff($start)->days > self::MAX_DAYS) {
            $this->period = Campaign::PERIOD_DAILY;

            $this->sourceData = array_map(
                function ($item) {
                    $chains = explode(' ', $item[$this->labelKey]);

                    $item[$this->labelKey] = reset($chains);

                    return $item;
                },
                $this->sourceData
            );

            return $this->getLabels();
        }

        $fulfilledLabels = [];
        $modifyPeriod    = $this->periodMap[$this->period];
        $start->modify(sprintf('-1 %s', $modifyPeriod));
        $end->modify(sprintf('+1 %s', $modifyPeriod));

        do {
            $fulfilledLabels[] = $start->format($format);

            $start->modify(sprintf('+1 %s', $modifyPeriod));
        } while ($end > $start);

        return $fulfilledLabels;
    }
}
