<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Type;

use OroCRM\Bundle\CampaignBundle\Entity\Campaign;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CampaignType extends AbstractType
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
                    'label'    => 'orocrm.campaign.name.label',
                    'required' => true,
                    'tooltip'  => 'orocrm.campaign.name.description',
                ]
            )
            ->add(
                'code',
                'text',
                [
                    'label'    => 'orocrm.campaign.code.label',
                    'required' => true,
                    'tooltip'  => 'orocrm.campaign.code.description',
                ]
            )
            ->add(
                'startDate',
                'oro_date',
                [
                    'label'    => 'orocrm.campaign.start_date.label',
                    'required' => false,
                ]
            )
            ->add(
                'endDate',
                'oro_date',
                [
                    'label'    => 'orocrm.campaign.end_date.label',
                    'required' => false,
                ]
            )->add(
                'description',
                'oro_resizeable_rich_text',
                [
                    'label'    => 'orocrm.campaign.description.label',
                    'required' => false,
                ]
            )
            ->add(
                'budget',
                'oro_money',
                [
                    'label'    => 'orocrm.campaign.budget.label',
                    'required' => false,
                ]
            )
            ->add(
                'reportPeriod',
                'choice',
                [
                    'label'   => 'orocrm.campaign.report_period.label',
                    'choices' => [
                        Campaign::PERIOD_HOURLY  => 'orocrm.campaign.report_period.hour',
                        Campaign::PERIOD_DAILY   => 'orocrm.campaign.report_period.day',
                        Campaign::PERIOD_MONTHLY => 'orocrm.campaign.report_period.month',
                    ],
                    'tooltip' => 'orocrm.campaign.report_period.description'
                ]
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'OroCRM\Bundle\CampaignBundle\Entity\Campaign',
            'validation_groups' => ['Campaign', 'Default']
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'orocrm_campaign_form';
    }
}
