<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

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

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory) {
                /** @var Channel $data */
                $data = $event->getData();
                $form = $event->getForm();

                if (null === $data) {
                    return;
                }

                // TODO get type from channel
                $type = 'magento';
                // TODO check if type is based on integration
                if ($type) {
                    // TODO get integration type from config
                    $integrationType = $type;
                    $integration     = new Integration();
                    $integration->setType($integrationType);

                    $integrationEmbeddedForm = $factory->createNamed(
                        'dataSource',
                        'oro_integration_channel_form',
                        $integration,
                        ['auto_initialize' => false]
                    );
                    $form->add($integrationEmbeddedForm);
                }
            }
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

            $this->appendClassAttr($view->children['owner']->vars, 'hide');
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

    /**
     * @param array  $options
     * @param string $cssClass
     */
    protected function appendClassAttr(array &$options, $cssClass)
    {
        $options['attr']          = isset($options['attr']) ? $options['attr'] : [];
        $options['attr']['class'] = isset($options['attr']['class']) ? $options['attr']['class'] : '';

        $options['attr']['class'] = implode(' ', [$options['attr']['class'], $cssClass]);
    }
}
