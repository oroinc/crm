<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Type;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

class EmailCampaignType extends AbstractType
{
    /**
     * @var EventSubscriberInterface[]
     */
    protected $subscribers = [];

    /**
     * @param EventSubscriberInterface $subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->subscribers as $subscriber) {
            $builder->addEventSubscriber($subscriber);
        }

        $builder
            ->add(
                'name',
                'text',
                ['label' => 'orocrm.campaign.emailcampaign.name.label']
            )
            ->add(
                'fromEmail',
                'text',
                [
                    'label'    => 'orocrm.campaign.emailcampaign.fromEmail.label',
                    'required' => false
                ]
            )
            ->add(
                'schedule',
                'choice',
                [
                    'choices' => [
                        EmailCampaign::SCHEDULE_IMMEDIATE => ucfirst(EmailCampaign::SCHEDULE_IMMEDIATE),
                        EmailCampaign::SCHEDULE_DEFERRED  => ucfirst(EmailCampaign::SCHEDULE_DEFERRED)
                    ],
                    'label'   => 'orocrm.campaign.emailcampaign.schedule.label',
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
                ['label' => 'orocrm.campaign.emailcampaign.campaign.label', 'required' => true]
            )
            ->add(
                'marketingList',
                'orocrm_marketing_list_select',
                ['label' => 'orocrm.campaign.emailcampaign.marketing_list.label', 'required' => true]
            )
            ->add(
                'template',
                'oro_email_template_list',
                [
                    'label'                   => 'orocrm.campaign.emailcampaign.template.label',
                    'depends_on_parent_field' => 'marketingList'
                ]
            )
            ->add('entityName', 'hidden', ['required' => false, 'mapped' => false])
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
