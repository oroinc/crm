<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter\Rest;

use Oro\Bundle\MagentoBundle\ImportExport\Converter\WebsiteDataConverter as BaseDataConverter;

/**
 * Class WebsiteDataConverter
 *
 * @deprecated This class is deprecated and will be removed in 2.4.
 *             Use Oro\Bundle\MagentoBundle\ImportExport\Converter\WebsiteDataConverter instead
 */
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
