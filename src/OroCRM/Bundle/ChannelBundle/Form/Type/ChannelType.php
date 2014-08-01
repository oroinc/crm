<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelType extends AbstractType
{
    const NAME = 'orocrm_channel_form';

    /** @var SettingsProvider */
    protected $settingsProvider;

    /**
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(SettingsProvider $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $builder->add(
            'name',
            'text',
            [
                'required' => true,
                'label'    => 'orocrm.channel.name.label'
            ]
        );
        $builder->add(
            'entities',
            'orocrm_channel_entity_choice_form',
            [
                'required' => false,
                'multiple' => true,
                'label'    => 'orocrm.channel.entities.label',
                'configs'  => ['placeholder' => 'orocrm.channel.form.select_entities.label']
            ]
        );
        $builder->add('dataSource', 'orocrm_channel_datasource_form');

//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            function (FormEvent $event) use ($factory) {
//                /** @var Channel $data */
//                $data = $event->getData();
//                $form = $event->getForm();
//
//                if (null === $data) {
//                    return;
//                }
//
//                // TODO get type from channel
//                $type = 'magento';
//                // TODO check if type is based on integration
//                if ($type) {
//                    // TODO get integration type from config
//                    $integrationType = $type;
//                    $integration     = new Integration();
//                    $integration->setType($integrationType);
//
//                    $integrationEmbeddedForm = $factory->createNamed(
//                        'dataSource',
//                        'oro_integration_channel_form',
//                        $integration,
//                        ['auto_initialize' => false]
//                    );
//                    $form->add($integrationEmbeddedForm);
//                }
//            }
//        );
        $builder->add(
            'channelType',
            'genemu_jqueryselect2_choice',
            [
                'choices'       => $this->settingsProvider->getChannelTypeChoiceList(),
                'required'      => true,
                'label'         => 'orocrm.channel.channel_type.label',
                'configs'       => ['placeholder' => 'orocrm.channel.form.select_channel_type.label'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($view->children['owner'], $view->children['owner']->vars['choices'])
            && count($view->children['owner']->vars['choices']) < 2
        ) {
            FormUtils::appendClass($view->children['owner'], 'hide');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'OroCRM\\Bundle\\ChannelBundle\\Entity\\Channel'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
