<?php

namespace Oro\Bundle\CampaignBundle\Form\Type;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
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
                    'label'    => 'oro.campaign.name.label',
                    'required' => true,
                    'tooltip'  => 'oro.campaign.name.description',
                ]
            )
            ->add(
                'code',
                'text',
                [
                    'label'    => 'oro.campaign.code.label',
                    'required' => true,
                    'tooltip'  => 'oro.campaign.code.description',
                ]
            )
            ->add(
                'startDate',
                'oro_date',
                [
                    'label'    => 'oro.campaign.start_date.label',
                    'required' => false,
                ]
            )
            ->add(
                'endDate',
                'oro_date',
                [
                    'label'    => 'oro.campaign.end_date.label',
                    'required' => false,
                ]
            )->add(
                'description',
                'oro_resizeable_rich_text',
                [
                    'label'    => 'oro.campaign.description.label',
                    'required' => false,
                ]
            )
            ->add(
                'budget',
                'oro_money',
                [
                    'label'    => 'oro.campaign.budget.label',
                    'required' => false,
                ]
            )
            ->add(
                'reportPeriod',
                'choice',
                [
                    'label'   => 'oro.campaign.report_period.label',
                    'choices' => [
                        Campaign::PERIOD_HOURLY  => 'oro.campaign.report_period.hour',
                        Campaign::PERIOD_DAILY   => 'oro.campaign.report_period.day',
                        Campaign::PERIOD_MONTHLY => 'oro.campaign.report_period.month',
                    ],
                    'tooltip' => 'oro.campaign.report_period.description'
                ]
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Oro\Bundle\CampaignBundle\Entity\Campaign',
            'validation_groups' => ['Campaign', 'Default']
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_campaign_form';
    }
}
