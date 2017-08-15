<?php

namespace Oro\Bundle\MagentoBundle\Converter\Rest;

use Oro\Bundle\MagentoBundle\Converter\RestResponseConverterInterface;

/**
 * Class WebsiteConverter is responsible for conversion Magento 2 website information to format expected by Connector
 */
class WebsiteConverter implements RestResponseConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($data)
    {
        foreach ($data as &$websiteData) {
            $websiteData['website_id'] = $websiteData['id'];
            unset($websiteData['id']);
        }
        unset($websiteData);

        return $data;
    }
}
