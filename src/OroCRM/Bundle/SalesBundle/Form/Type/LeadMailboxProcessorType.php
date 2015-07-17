<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeadMailboxProcessorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'OroCRM\Bundle\SalesBundle\Entity\LeadMailboxProcessorSettings',
            'cascade_validation' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('owner', 'oro_user_select', [
            'required' => true,
            'label'    => 'orocrm.sales.lead.owner.label',
        ])->add(
            'channel',
            'orocrm_channel_select_type',
            [
                'required' => true,
                'label'    => 'orocrm.sales.lead.data_channel.label',
                'entities' => [
                    'OroCRM\\Bundle\\SalesBundle\\Entity\\Lead'
                ],
            ]
        )->add(
            'source',
            'oro_enum_select',
            [
                'required'  => false,
                'label'     => 'orocrm.sales.lead.source.label',
                'enum_code' => 'lead_source'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_sales_mailbox_processor_lead';
    }
}
