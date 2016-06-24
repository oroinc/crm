<?php

namespace OroCRM\Bundle\SalesBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * Handle the additional 'probability' field added to Opportunity Enum Options.
 * Maps probability per opportunity status
 * Stores and retrieves the map from the scoped System Config
 */
class OpportunityStatusConfigExtension extends AbstractTypeExtension
{
    const CONFIG_KEY = 'oro_crm_sales.default_opportunity_probabilities';

    /** @var ConfigManager */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
        // bind with lower priority, we need populated 'enum_options'
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData'], -1);
    }

    /**
     * Pre set data event handler
     * Populate probability fields from the System Config (scoped)
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $probabilityConfig = $this->configManager->get(self::CONFIG_KEY);

        if (empty($data['enum'])) {
            return;
        }

        foreach ($data['enum']['enum_options'] as $key => $enum_option) {
            if (isset($probabilityConfig[$enum_option['id']])) {
                $data['enum']['enum_options'][$key]['probability'] = $probabilityConfig[$enum_option['id']];
            }
        }

        $event->setData($data);
    }

    /**
     * Submit event handler
     * Cleanup empty rows and
     * Store the opportunity-probability map into the System Config (scoped)
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();

        // cleanup empty enum options
        foreach ($data['enum']['enum_options'] as $key => $enum_option) {
            if (empty($enum_option['label'])) {
                unset($data['enum']['enum_options'][$key]);
            }
        }

        $event->setData($data);

        $value = [];
        foreach ($data['enum']['enum_options'] as $enum_option) {
            $value[$enum_option['id']] = isset($enum_option['probability']) ? $enum_option['probability'] : null;
        }

        $this->configManager->set(self::CONFIG_KEY, $value);
        $this->configManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'orocrm_sales_opportunity_status_config';
    }
}
