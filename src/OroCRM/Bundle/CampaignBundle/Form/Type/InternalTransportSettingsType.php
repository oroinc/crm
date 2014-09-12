<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InternalTransportSettingsType extends AbstractTransportSettingsType
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
                    'required' => true,
                    'depends_on_parent_field' => 'marketingList',
                    'data_route' => 'orocrm_api_get_emailcampaign_email_templates',
                    'data_route_parameter' => 'id'
                ]
            );

        parent::buildForm($builder, $options);
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
