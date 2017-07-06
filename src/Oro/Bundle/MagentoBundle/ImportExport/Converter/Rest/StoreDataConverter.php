<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter\Rest;

use Oro\Bundle\MagentoBundle\ImportExport\Converter\StoreDataConverter as BaseDataConverter;

class StoreDataConverter extends BaseDataConverter
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
            'website_id' => 'website:originId'
        ];
    }
}
