<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class WebsiteSoapIterator extends AbstractLoadeableSoapIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getData()
    {
        $websites = [];

        if ($this->transport->isSupportedExtensionVersion()) {
            $websites = $this->transport->call(SoapTransport::ACTION_ORO_WEBSITE_LIST);
            $websites = ConverterUtils::objectToArray($websites);
        } else {
            $stores = $this->transport->getStores();
            foreach ($stores as $store) {
                $websites[$store['website_id']]['name'][] = $store['name'];
                $websites[$store['website_id']]['code'][] = $store['code'];
            }

            foreach ($websites as $websiteId => $websiteItem) {
                $websites[$websiteId]['name'] = implode(SoapTransport::WEBSITE_NAME_SEPARATOR, $websiteItem['name']);
                $websites[$websiteId]['code'] = implode(SoapTransport::WEBSITE_CODE_SEPARATOR, $websiteItem['code']);
                $websites[$websiteId]['website_id'] = $websiteId;
            }
        }

        return $websites;
    }
}
