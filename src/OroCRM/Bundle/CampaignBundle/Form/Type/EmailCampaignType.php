<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EmailBundle\Form\EventListener\BuildTemplateFormSubscriber;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

class EmailCampaignType extends AbstractType
{
    /**
     * @var BuildTemplateFormSubscriber
     */
    protected $subscriber;

    /**
     * @param BuildTemplateFormSubscriber $subscriber
     */
    public function __construct(BuildTemplateFormSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->subscriber);

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
                    'choices'  => [
                        EmailCampaign::SCHEDULE_IMMEDIATE => ucfirst(EmailCampaign::SCHEDULE_IMMEDIATE),
                        EmailCampaign::SCHEDULE_DEFERRED  => ucfirst(EmailCampaign::SCHEDULE_DEFERRED)
                    ],
                    'label'    => 'orocrm.campaign.emailcampaign.schedule.label',
                    'required' => true
                ]
            )
            ->add(
                'scheduledAt',
                'oro_datetime',
                [
                    'label'    => 'orocrm.campaign.emailcampaign.scheduled_at.label',
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
                    'label'                   => 'orocrm.campaign.emailcampaign.template.label',
                    'required'                => true,
                    'depends_on_parent_field' => 'marketingList'
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
