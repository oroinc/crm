<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter\Rest;

use Oro\Bundle\MagentoBundle\ImportExport\Converter\WebsiteDataConverter as BaseDataConverter;

class WebsiteDataConverter extends BaseDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'id' => 'originId',
            'code' => 'code',
            'name' => 'name',
            'default_group_id' => 'defaultGroupId'
        ];
    }
}
