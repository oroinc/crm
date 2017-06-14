<?php

namespace Oro\Bundle\MagentoBundle\Converter\Rest;

use Oro\Bundle\MagentoBundle\Converter\RestResponseConverterInterface;

class RegionConverter implements RestResponseConverterInterface
{
    /**
     * @param $data
     *
     * @return array
     */
    public function convert($data)
    {
        $regions = [];
        foreach ($data as $countryItem) {
            if (isset($countryItem['available_regions'])) {
                foreach ($countryItem['available_regions'] as $regionItem) {
                    $regionItem['region_id'] = $regionItem['id'];
                    $regionItem['countryCode'] = $countryItem['two_letter_abbreviation'];
                    unset($regionItem['id']);

                    $regions[$regionItem['code']] = $regionItem;
                }
            }
        }

        return $regions;
    }
}
