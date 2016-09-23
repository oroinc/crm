<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class CustomerGroupDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'customer_group_id' => 'originId',
            'customer_group_code' => 'name'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }

    /**
     * {@inheritdoc}
     */
    protected function fillEmptyColumns(array $header, array $data)
    {
        $dataDiff = array_diff(array_keys($data), $header);
        $data = array_diff_key($data, array_flip($dataDiff));

        return parent::fillEmptyColumns($header, $data);
    }
}
