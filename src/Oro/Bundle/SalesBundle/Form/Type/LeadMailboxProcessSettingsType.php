<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\Type\OrganizationUserAclSelectType;
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
                'data_class' => 'Oro\Bundle\SalesBundle\Entity\LeadMailboxProcessSettings',
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
            OrganizationUserAclSelectType::class,
            [
                'required' => true,
                'label'    => 'oro.sales.lead.owner.label',
                'constraints' => [
                    new NotNull()
                ]
            ]
        )->add(
            'source',
            LeadMailboxProcessSourceType::class,
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
