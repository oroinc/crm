<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Form\Type\ConfigScopeType;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Manage Opportunity Status Enum options from the System Config
 * FormType is extended by {@see Oro\Bundle\EntityExtendBundle\Form\Extension\EnumFieldConfigExtension}
 */
class OpportunityStatusEnumType extends AbstractType
{
    const NAME = 'orocrm_sales_opportunity_status_enum';

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
        $configType = PropertyConfigContainer::TYPE_FIELD;

        $provider = $this->configManager->getProvider('enum');
        $items = $provider->getPropertyConfig()->getFormItems($configType, 'enum');

        $config = $this->configManager->getConfig($options['config_id']);

        // clean form options and leave only those needed by System Config layout
        $items['enum_options']['form']['options'] = array_intersect_key(
            $items['enum_options']['form']['options'],
            array_flip(['label', 'tooltip'])
        );

        $builder->add(
            'enum',
            new ConfigScopeType($items, $config, $this->configManager, $options['config_model']),
            [
                'label' => false,
            ]
        );

        $builder->setData(
            [
                'enum' => $config->all(),
            ]
        );
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
                'config_is_new' => !$configModel->getId(),
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
