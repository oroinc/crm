<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChannelType extends AbstractType
{
    const NAME = 'orocrm_channel_form';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            [
                'required' => true,
                'label'    => 'orocrm.channel.name.label'
            ]
        );
        $builder->add(
            'description',
            'textarea',
            [
                'required' => true,
                'label'    => 'orocrm.channel.description.label'
            ]
        );
        $builder->add(
            'integrations',
            'genemu_jqueryselect2_entity',
            [
                'required' => false,
                'multiple' => true,
                'label'    => 'orocrm.channel.integrations.label',
                'class'    => 'Oro\Bundle\IntegrationBundle\Entity\Channel',
                'configs'  => ['placeholder' => 'orocrm.channel.form.select_integrations.label'],

            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'OroCRM\\Bundle\\ChannelBundle\\Entity\\Channel',
                'intention'          => 'channel'
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
