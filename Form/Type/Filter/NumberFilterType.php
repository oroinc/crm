<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NumberFilterType extends AbstractType
{
    const TYPE_GREATER_EQUAL = 1;
    const TYPE_GREATER_THAN = 2;
    const TYPE_EQUAL = 3;
    const TYPE_LESS_EQUAL = 4;
    const TYPE_LESS_THAN = 5;
    const NAME = 'oro_type_number_filter';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return FilterType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $operatorChoices = array(
            self::TYPE_EQUAL => $this->translator->trans('label_type_equal', array(), 'OroFilterBundle'),
            self::TYPE_GREATER_EQUAL =>
                $this->translator->trans('label_type_greater_equal', array(), 'OroFilterBundle'),
            self::TYPE_GREATER_THAN => $this->translator->trans('label_type_greater_than', array(), 'OroFilterBundle'),
            self::TYPE_LESS_EQUAL => $this->translator->trans('label_type_less_equal', array(), 'OroFilterBundle'),
            self::TYPE_LESS_THAN => $this->translator->trans('label_type_less_than', array(), 'OroFilterBundle'),
        );

        $resolver->setDefaults(
            array(
                'field_type' => 'number',
                'operator_choices' => $operatorChoices,
            )
        );
    }
}
