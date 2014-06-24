<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;
use Oro\Bundle\ImportExportBundle\Field\FieldHelper;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

class CompositeNormalizer extends ConfigurableEntityNormalizer
{
    /**
     * @var AbstractTableDataConverter
     */
    protected $dataConverter;

    /**
     * @param FieldHelper $fieldHelper
     * @param AbstractTableDataConverter $dataConverter
     */
    public function __construct(FieldHelper $fieldHelper, AbstractTableDataConverter $dataConverter)
    {
        parent::__construct($fieldHelper);
        $this->dataConverter = $dataConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $data = $this->dataConverter->convertToImportFormat($data);
        return parent::denormalize($data, $class, $format, $context);
    }
}
