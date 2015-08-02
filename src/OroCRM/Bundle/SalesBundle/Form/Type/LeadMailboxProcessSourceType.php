<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(
            [
                'choices' => $this->getChoices(),
            ]
        );
    }

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
