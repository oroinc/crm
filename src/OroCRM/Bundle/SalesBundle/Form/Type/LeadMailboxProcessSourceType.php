<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;

class LeadMailboxProcessSourceType extends AbstractType
{
    /** @var EnumValueProvider */
    private $enumValueProvider;

    /**
     * LeadMailboxProcessSourceType constructor.
     *
     * @param EnumValueProvider $enumValueProvider
     */
    public function __construct(EnumValueProvider $enumValueProvider)
    {
        $this->enumValueProvider = $enumValueProvider;
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
        return $this->enumValueProvider->getEnumChoicesByCode('lead_source');
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
        return 'orocrm_sales_lead_mailbox_process_source';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_choice';
    }
}
