<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

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
                'data_class'         => 'Oro\Bundle\SalesBundle\Entity\LeadMailboxProcessSettings',
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
                'label'    => 'oro.sales.lead.owner.label',
                'constraints' => [
                    new NotNull()
                ]
            ]
        )->add(
            'channel',
            'oro_channel_select_type',
            [
                'required' => true,
                'label'    => 'oro.sales.lead.data_channel.label',
                'entities' => [
                    'Oro\\Bundle\\SalesBundle\\Entity\\Lead'
                ],
                'constraints' => [
                    new NotNull()
                ]
            ]
        )->add(
            'source',
            'oro_sales_lead_mailbox_process_source',
            [
                'required'    => true,
                'label'       => 'oro.sales.lead.source.label',
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
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_sales_lead_mailbox_process_settings';
    }
}
