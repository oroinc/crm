<?php

namespace OroCRM\Bundle\CampaignBundle\Model\Data\Transformer;

use Oro\Bundle\ChartBundle\Model\Data\ArrayData;
use Oro\Bundle\ChartBundle\Model\Data\DataInterface;
use Oro\Bundle\ChartBundle\Model\Data\MappedData;
use Oro\Bundle\ChartBundle\Model\Data\Transformer\TransformerInterface;

class MultiLineDataTransformer implements TransformerInterface
{
    /**
     * @param DataInterface $data
     * @param array         $chartOptions
     *
     * @return DataInterface
     */
    public function transform(DataInterface $data, array $chartOptions)
    {
        /** @var MappedData $data */
        $sourceData = $data->getSourceData()->toArray();
        $data       = $data->toArray();
        $arrayData  = [];

        foreach ($sourceData as $sourceDataKey => $sourceDataItem) {
            $dataItem = $data[$sourceDataKey];

            $keys = array_diff(
                $sourceDataItem,
                $dataItem
            );

            foreach ($keys as $key) {
                $arrayData[$key][] = $dataItem;
            }
        }

        return new ArrayData($arrayData);
    }
}
