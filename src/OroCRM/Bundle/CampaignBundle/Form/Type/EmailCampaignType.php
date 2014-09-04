<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EmailCampaignType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                [
                    'label'    => 'orocrm.email_campaign.name.label',
                    'required' => true
                ]
            )
            ->add(
                'campaign',
                'orocrm_campaign_select',
                [
                    'label'    => 'orocrm.email_campaign.campaign.label',
                    'required' => true
                ]
            )
            ->add(
                'marketingList',
                'orocrm_marketing_list_select',
                [
                    'label'    => 'orocrm.email_campaign.marketing_list.label',
                    'required' => true
                ]
            )
            ->add(
                'description',
                'textarea',
                [
                    'label'    => 'orocrm.email_campaign.description.label',
                    'required' => false,
                ]
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => 'OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'orocrm_email_campaign';
    }
}
