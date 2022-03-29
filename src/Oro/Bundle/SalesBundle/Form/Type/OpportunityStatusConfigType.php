<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager as EntityConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Form\Type\ConfigScopeType;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Manage Opportunity statuses from the System Config
 * Maps probability per opportunity status
 */
class OpportunityStatusConfigType extends AbstractType
{
    const NAME = 'oro_sales_opportunity_status_config';

    /** @var EntityConfigManager */
    protected $entityConfigManager;

    /** @var ConfigIdInterface */
    protected $configId;

    /** @var FieldConfigModel */
    protected $configModel;

    /** @var ConfigProvider */
    protected $enumProvider;

    /** @var EventSubscriberInterface */
    protected $eventSubscriber;

    /** @var ConfigManager */
    protected $configManager;

    /** @var EventSubscriberInterface */
    protected $enumEventSubscriber;

    public function __construct(
        EntityConfigManager $entityConfigManager,
        ConfigManager $configManager,
        EventSubscriberInterface $enumEventSubscriber
    ) {
        $this->configManager = $configManager;
        $this->enumEventSubscriber = $enumEventSubscriber;
        $this->entityConfigManager = $entityConfigManager;
        $this->enumProvider = $entityConfigManager->getProvider('enum');
        $this->configId = $this->enumProvider->getId(Opportunity::class, 'status', 'enum');
        $this->configModel = $entityConfigManager->getConfigFieldModel(Opportunity::class, 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->entityConfigManager->getConfig($this->configId);
        $items = $this->enumProvider->getPropertyConfig()->getFormItems(PropertyConfigContainer::TYPE_FIELD, 'enum');

        // replace items type with the extended form that includes 'probability'
        // clean form options and leave only those needed by System Config layout
        $items['enum_options']['form']['options'] = [
            'entry_type' => OpportunityStatusEnumValueType::class,
            'label' => 'oro.sales.system_configuration.groups.opportunity_status_probabilities.options.label',
            'tooltip' => 'oro.sales.system_configuration.groups.opportunity_status_probabilities.options.tooltip',
        ];

        $builder->add(
            'enum',
            ConfigScopeType::class,
            [
                'items' => $items,
                'config' => $config,
                'config_model' => $options['config_model'],
                'label' => false,
            ]
        );

        $builder->setData(['enum' => $config->all(), 'use_parent_scope_value' => false]);

        // manage enum options (add/remove/reorder/etc.)
        $builder->addEventSubscriber($this->enumEventSubscriber);
        // bind before enumEventSubscriber clears the submitted data
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
        // bind with lower priority, we need populated 'enum_options'
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'], -1);
    }

    /**
     * Pre set data event handler
     * Populate probability fields from the System Config (scoped)
     */
    public function onPreSetData(FormEvent $event)
    {
        $data = $event->getData();
        $probabilityConfig = $this->configManager->get(Opportunity::PROBABILITIES_CONFIG_KEY);

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
     * Store the opportunity-probability map into the System Config (scoped)
     */
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $value = [];

        foreach ($data['enum']['enum_options'] as $enum_option) {
            if (empty($enum_option['label'])) {
                continue;
            }
            $id = $enum_option['id'];

            if (empty($id)) {
                // enum_option is just added and still does not have a generated id
                // we generate one, because we bind before the option is persisted
                $id = ExtendHelper::buildEnumValueId($enum_option['label']);
            }

            $value[$id] = isset($enum_option['probability']) ? $enum_option['probability'] : null;
        }

        $this->configManager->set(Opportunity::PROBABILITIES_CONFIG_KEY, $value);
        $this->configManager->flush();
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
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
