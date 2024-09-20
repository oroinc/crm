<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Select form type for Lead Mailbox Process Source.
 */
class LeadMailboxProcessSourceType extends AbstractType
{
    /** @var EnumOptionsProvider */
    private $enumOptionsProvider;

    /**
     * LeadMailboxProcessSourceType constructor.
     */
    public function __construct(EnumOptionsProvider $enumOptionsProvider)
    {
        $this->enumOptionsProvider = $enumOptionsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            [
                'choices' => $this->getChoices(),
            ]
        );
    }

    /**
     * Returns array of choices for this field.
     *
     * @return array['value' => 'label (translatable id)']
     */
    protected function getChoices()
    {
        return $this->enumOptionsProvider->getEnumChoicesByCode('lead_source');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->resetModelTransformers();
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
    public function getBlockPrefix(): string
    {
        return 'oro_sales_lead_mailbox_process_source';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return Select2ChoiceType::class;
    }
}
