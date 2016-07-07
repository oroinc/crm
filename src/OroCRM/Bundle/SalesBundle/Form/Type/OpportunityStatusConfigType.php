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
 * Manage Opportunity statuses from the System Config
 * FormType is extended by:
 * - EntityExtendBundle::EnumFieldConfigExtension to manage enum options
 * - SalesBundle::OpportunityStatusConfigExtension to manage the probability map
 *
 * @see Oro\Bundle\EntityExtendBundle\Form\Extension\EnumFieldConfigExtension
 * @see OroCRM\Bundle\SalesBundle\Form\Extension\OpportunityStatusConfigExtension
 */
class OpportunityStatusConfigType extends AbstractType
{
    const NAME = 'orocrm_sales_opportunity_status_config';

    /** @var ConfigManager */
    protected $configManager;

    /** @var \Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface */
    protected $configId;

    /** @var \Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel */
    protected $configModel;

    /** @var \Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider */
    protected $enumProvider;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
        $this->enumProvider = $configManager->getProvider('enum');
        $this->configId = $this->enumProvider->getId(Opportunity::class, 'status', 'enum');
        $this->configModel = $configManager->getConfigFieldModel(Opportunity::class, 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->configManager->getConfig($this->configId);
        $items = $this->enumProvider->getPropertyConfig()->getFormItems(PropertyConfigContainer::TYPE_FIELD, 'enum');

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
        $resolver->setDefaults(
            [
                'config_model' => $this->configModel,
                'config_id' => $this->configId,
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
