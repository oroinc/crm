<?php

namespace OroCRM\Bundle\CampaignBundle\Model\Data\Transformer;

use Oro\Component\PhpUtils\ArrayUtil;

use Oro\Bundle\ChartBundle\Model\Data\ArrayData;
use Oro\Bundle\ChartBundle\Model\Data\DataInterface;
use Oro\Bundle\ChartBundle\Model\Data\MappedData;
use Oro\Bundle\ChartBundle\Model\Data\Transformer\TransformerInterface;

use OroCRM\Bundle\CampaignBundle\Entity\Campaign;

class MultiLineDataTransformer implements TransformerInterface
{
    /**
     * Force next scale if more than N items of previous scale where used
     */
    const MAX = 40;

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
    protected $dateFormatMap = [
        Campaign::PERIOD_HOURLY  => 'Y-m-d H:i:s',
        Campaign::PERIOD_DAILY   => 'Y-m-d',
        Campaign::PERIOD_MONTHLY => 'Y-m',
        Campaign::PERIOD_YEARLY  => 'Y',
    ];

    /**
     * @var array
     */
    protected $dateFormatSize = [];

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

        $keys   = array_unique(ArrayUtil::arrayColumn($this->sourceData, $this->groupingOption));
        $values = array_combine($keys, array_fill(0, sizeof($keys), $this->getLabels()));

        foreach ($values as $group => &$value) {
            array_walk(
                $value,
                function (&$label) use ($group) {
                    $counts = array_map(
                        function ($item) use ($label, $group) {
                            if ($item[$this->groupingOption] == $group && $item[$this->labelKey] == $label) {
                                return $item[$this->valueKey];
                            }

                            return 0;
                        },
                        $this->sourceData
                    );

                    $label = ['label' => $label, 'value' => array_sum($counts)];
                }
            );
        }

        return new ArrayData($values);
    }

    /**
     * @param DataInterface $data
     * @param array         $chartOptions
     *
     * @throws \InvalidArgumentException
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

        $now = new \DateTime();
        foreach ($this->dateFormatMap as $key => $format) {
            $this->dateFormatSize[$key] = strlen($now->format($format));
        }
    }

    /**
     * @return array
     */
    protected function getLabels()
    {
        $labels = [];
        foreach ($this->sourceData as &$sourceDataValue) {
            $sourceDataValue[$this->labelKey] = substr(
                $sourceDataValue[$this->labelKey],
                0,
                $this->dateFormatSize[$this->period]
            );

            $labels[] = $sourceDataValue[$this->labelKey];
        }
        asort($labels);

        $format = $this->dateFormatMap[$this->period];
        $start  = \DateTime::createFromFormat($format, reset($labels));
        $end    = \DateTime::createFromFormat($format, end($labels));

        if (!$start || !$end) {
            return array_unique($labels);
        }

        $fulfilledLabels = [];
        $start->modify(sprintf('-1 %s', $this->period));
        $end->modify(sprintf('+1 %s', $this->period));

        do {
            $fulfilledLabels[] = $start->format($format);

            if (sizeof($fulfilledLabels) > self::MAX) {
                $next = false;
                foreach (array_keys($this->dateFormatMap) as $period) {
                    if ($next === true) {
                        $this->period = $period;

                        return $this->getLabels();
                    }

                    if ($this->period == $period) {
                        $next = true;
                    }
                }

                return array_unique($labels);
            }

            $start->modify(sprintf('+1 %s', $this->period));
        } while ($end > $start);

        return $fulfilledLabels;
    }
}
