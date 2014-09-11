<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InternalTransportSettingsType extends AbstractType
{
    const NAME = 'orocrm_campaign_internal_transport_settings';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'template',
                'oro_email_template_list',
                [
                    'label' => 'orocrm.campaign.emailcampaign.template.label',
                    'required' => true
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
                'data_class' => 'OroCRM\Bundle\CampaignBundle\Entity\InternalTransportSettings'
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
