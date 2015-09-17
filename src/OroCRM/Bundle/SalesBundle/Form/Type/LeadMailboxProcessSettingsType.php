<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class LeadMailboxProcessSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'OroCRM\Bundle\SalesBundle\Entity\LeadMailboxProcessSettings',
                'cascade_validation' => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'owner',
            'oro_user_organization_acl_select',
            [
                'required' => true,
                'label'    => 'orocrm.sales.lead.owner.label',
                'constraints' => [
                    new NotNull()
                ]
            ]
        )->add(
            'channel',
            'orocrm_channel_select_type',
            [
                'required' => true,
                'label'    => 'orocrm.sales.lead.data_channel.label',
                'entities' => [
                    'OroCRM\\Bundle\\SalesBundle\\Entity\\Lead'
                ],
                'constraints' => [
                    new NotNull()
                ]
            ]
        )->add(
            'source',
            'orocrm_sales_lead_mailbox_process_source',
            [
                'required'    => true,
                'label'       => 'orocrm.sales.lead.source.label',
                'multiple'    => false,
                'expanded'    => false,
                'constraints' => [
                    new NotNull(),
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_sales_lead_mailbox_process_settings';
    }
}
