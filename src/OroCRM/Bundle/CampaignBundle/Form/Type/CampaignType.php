<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Type;

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
                ]
            )
            ->add(
                'code',
                'text',
                [
                    'label'    => 'orocrm.campaign.code.label',
                    'required' => true,
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
                'textarea',
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
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => 'OroCRM\Bundle\CampaignBundle\Entity\Campaign']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'orocrm_campaign_form';
    }
}
