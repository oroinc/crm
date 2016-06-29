<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Form\Type\ConfigScopeType;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Manage Opportunity Status Enum options from the System Config
 * FormType is extended by:
 * {@link Oro\Bundle\EntityExtendBundle\Form\Extension\EnumFieldConfigExtension} to retrieve/store enum options
 * and
 * {@link OroCRM\Bundle\SalesBundle\Form\Extension\OpportunityStatusConfigExtension} to retrieve/store a probability map
 */
class OpportunityStatusConfigType extends AbstractType
{
    const NAME = 'orocrm_sales_opportunity_status_config';

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
        $provider = $this->configManager->getProvider('enum');
        $config = $this->configManager->getConfig($options['config_id']);
        $items = $provider->getPropertyConfig()->getFormItems(PropertyConfigContainer::TYPE_FIELD, 'enum');

        // clean form options and leave only those needed by System Config layout
        $items['enum_options']['form']['options'] = [
            'label' => false,
        ];

        // replace items type with the extended form that includes 'probability'
        $items['enum_options']['form']['options']['type'] = 'orocrm_sales_opportunity_status_enum_value';

        $builder->add(
            'enum',
            new ConfigScopeType($items, $config, $this->configManager, $options['config_model']),
            [
                'label' => false,
            ]
        );
        $builder->setData(['enum' => $config->all(), 'use_parent_scope_value' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $className = Opportunity::class;
        $configModel = $this->configManager->getConfigFieldModel($className, 'status');
        $provider = $this->configManager->getProvider('enum');
        $configId = $provider->getId($className, 'status', 'enum');

        $resolver->setDefaults(
            [
                'config_model' => $configModel,
                'config_id' => $configId,
                'config_is_new' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
