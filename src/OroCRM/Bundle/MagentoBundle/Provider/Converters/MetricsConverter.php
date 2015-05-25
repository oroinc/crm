<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Converters;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DashboardBundle\Provider\ConfigValueConverterAbstract;

class MetricsConverter extends ConfigValueConverterAbstract
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getConvertedValue(array $widgetConfig, $value = null, array $config = [], array $options = [])
    {
        if ($value === null) {
            return $this->getDefaultValue($widgetConfig['data_items']);
        }

        return parent::getConvertedValue($widgetConfig, $value, $config, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getViewValue($value)
    {
        return json_encode($value);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultValue($dataItems = [])
    {
        return array_keys($dataItems);
    }
}