<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Type;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
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
                    'label'    => 'orocrm.campaign.emailcampaign.name.label',
                    'required' => true
                ]
            )
            ->add(
                'schedule',
                'choice',
                [
                    'choices' => [
                        EmailCampaign::SCHEDULE_IMMEDIATE => ucfirst(EmailCampaign::SCHEDULE_IMMEDIATE),
                        EmailCampaign::SCHEDULE_DEFERRED => ucfirst(EmailCampaign::SCHEDULE_DEFERRED)
                    ],
                    'label' => 'orocrm.campaign.emailcampaign.schedule.label',
                    'required' => true
                ]
            )
            ->add(
                'scheduledAt',
                'oro_datetime',
                [
                    'label' => 'orocrm.campaign.emailcampaign.scheduled_at.label',
                    'required' => false
                ]
            )
            ->add(
                'campaign',
                'orocrm_campaign_select',
                [
                    'label'    => 'orocrm.campaign.emailcampaign.campaign.label',
                    'required' => true
                ]
            )
            ->add(
                'marketingList',
                'orocrm_marketing_list_select',
                [
                    'label'    => 'orocrm.campaign.emailcampaign.marketing_list.label',
                    'required' => true
                ]
            )
            ->add(
                'template',
                'oro_email_template_list',
                [
                    'label'    => 'orocrm.campaign.emailcampaign.template.label',
                    'required' => true
                ]
            )
            ->add(
                'description',
                'textarea',
                [
                    'label'    => 'orocrm.campaign.emailcampaign.description.label',
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
