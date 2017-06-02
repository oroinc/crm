<?php

namespace Oro\Bundle\MagentoBundle\Converter\Rest;

use Oro\Bundle\MagentoBundle\Converter\RestResponseConverterInterface;

class RegionConverter implements RestResponseConverterInterface
{
    protected $map = [
        'country_id' => 'id',
        'iso2_code' => 'two_letter_abbreviation',
        'iso3_code' => 'three_letter_abbreviation',
        'name' => 'full_name_english'
    ];

    /**
     * @param $data
     *
     * @return array
     */
    public function convert($data)
    {
        $convertedData = [];

        foreach ($data as $countryItem) {
            $country = [];

            $this->copySimpleProperties($country, $countryItem);
            $this->copyRegions($country, $countryItem);

            $convertedData[$country['iso2_code']] = $country;
        }

        return $convertedData;
    }

    /**
     * @param $country
     * @param $countryItem
     */
    protected function copySimpleProperties(&$country, $countryItem)
    {
        foreach ($this->map as $supportedKey => $responseKey) {
            $country[$supportedKey] = '';
            if (isset($countryItem[$responseKey])) {
                $country[$supportedKey] = $countryItem[$responseKey];
            }
        }
    }

    /**
     * @param $country
     * @param $countryItem
     */
    protected function copyRegions(&$country, $countryItem)
    {
        if (isset($countryItem['available_regions'])) {
            foreach ($countryItem['available_regions'] as $regionItem) {
                $regionItem['region_id'] = $regionItem['id'];
                unset($regionItem['id']);

                $country['available_regions'][$regionItem['code']] = $regionItem;
            }
        }
    }
}
