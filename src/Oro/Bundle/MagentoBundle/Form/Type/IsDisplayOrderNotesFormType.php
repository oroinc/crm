<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IsDisplayOrderNotesFormType extends AbstractType
{
    const NAME = 'oro_magento_is_display_order_notes_type';

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'oro.magento.magentotransport.is_display_order_notes.label',
            'tooltip' => 'oro.magento.magentotransport.is_display_order_notes.tooltip',
            'choices' => [
                'oro.magento.magentotransport.is_display_order_notes.value.true.label' => true,
                'oro.magento.magentotransport.is_display_order_notes.value.false.label' => false,
            ],
            'placeholder' => false,
            'required' => false
        ]);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return ChoiceType::class;
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
        return self::NAME;
    }
}
