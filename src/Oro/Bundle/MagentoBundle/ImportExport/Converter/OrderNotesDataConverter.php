<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class OrderNotesDataConverter extends IntegrationAwareDataConverter
{
    /** {@inheritdoc} */
    protected function getHeaderConversionRules()
    {
        return [
            'increment_id' => 'originId',
            'created_at'   => 'createdAt',
            'updated_at'   => 'updatedAt',
            'comment'      => 'message'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        // will be implemented for bidirectional sync
        throw new \Exception('Normalization is not implemented!');
    }
}
