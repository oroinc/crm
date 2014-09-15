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
                'senderEmail',
                'text',
                [
                    'label'    => 'orocrm.campaign.emailcampaign.senderEmail.label',
                    'required' => false
                ]
            )
            ->add(
                'senderName',
                'text',
                [
                    'label'    => 'orocrm.campaign.emailcampaign.senderName.label',
                    'required' => false
                ]
            )
            ->add(
                'schedule',
                'choice',
                [
                    'choices' => [
                        EmailCampaign::SCHEDULE_MANUAL   => ucfirst(EmailCampaign::SCHEDULE_MANUAL),
                        EmailCampaign::SCHEDULE_DEFERRED => ucfirst(EmailCampaign::SCHEDULE_DEFERRED)
                    ],
                    'label'   => 'orocrm.campaign.emailcampaign.schedule.label',
                ]
            )
            ->add(
                'scheduledFor',
                'oro_datetime',
                [
                    'label'    => 'orocrm.campaign.emailcampaign.scheduled_for.label',
                    'required' => false,
                    'attr'     => [
                        'data-validation-optional-group' => true,
                        'data-validation-ignore'         => true,
                        'data-validation'                => json_encode(['DateTime' => [], 'NotBlank' => []])
                    ]
                ]
            )
            ->add(
                'campaign',
                'orocrm_campaign_select',
                ['label' => 'orocrm.campaign.emailcampaign.campaign.label']
            )
            ->add(
                'marketingList',
                'orocrm_marketing_list_select',
                ['label' => 'orocrm.campaign.emailcampaign.marketing_list.label', 'required' => true]
            )
            ->add(
                'transport',
                'orocrm_campaign_email_transport_select',
                [
                    'label'    => 'orocrm.campaign.emailcampaign.transport.label',
                    'required' => true,
                    'mapped'   => false
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
        $resolver->setDefaults(
            [
                'data_class' => 'OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign',
                'cascade_validation' => true
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'orocrm_email_campaign';
    }
}
