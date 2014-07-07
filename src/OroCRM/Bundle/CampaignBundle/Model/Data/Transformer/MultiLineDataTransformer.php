<?php

namespace OroCRM\Bundle\CampaignBundle\Model\Data\Transformer;

use Oro\Bundle\ChartBundle\Model\Data\ArrayData;
use Oro\Bundle\ChartBundle\Model\Data\DataInterface;
use Oro\Bundle\ChartBundle\Model\Data\MappedData;
use Oro\Bundle\ChartBundle\Model\Data\Transformer\TransformerInterface;

class MultiLineDataTransformer implements TransformerInterface
{
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
     * @var array
     */
    protected $periodMap = [
        'hourly'  => 'hour',
        'daily'   => 'day',
        'monthly' => 'month'
    ];

    /**
     * @var array
     */
    protected $dateFormatMap = [
        'hour'  => 'Y-m-d H:i:s.u',
        'day'   => 'Y-m-d',
        'month' => 'Y-m-d'
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

        $labels = $this->getLabels($this->sourceData, $this->labelKey);

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

        if (!empty($chartOptions['default_settings']['period'])) {
            $this->period = $this->periodMap[$chartOptions['default_settings']['period']];
        }
    }

    /**
     * @param array  $sourceData
     * @param string $labelKey
     * @return array
     */
    protected function getLabels(array $sourceData, $labelKey)
    {
        $labels = [];
        foreach ($sourceData as $sourceDataValue) {
            $labels[] = $sourceDataValue[$labelKey];
        }
        asort($labels);

        if (!$this->period) {
            return array_unique($labels);
        }

        $format = $this->dateFormatMap[$this->period];
        $start  = \DateTime::createFromFormat($format, reset($labels));
        $end    = \DateTime::createFromFormat($format, end($labels));
        $end->modify(sprintf('+1 %s', $this->period));

        $fulfilledLabels = [];
        do {
            $fulfilledLabels[] = $start->format($format);

            $start->modify(sprintf('+1 %s', $this->period));
        } while ($end > $start);

        return $fulfilledLabels;
    }
}
