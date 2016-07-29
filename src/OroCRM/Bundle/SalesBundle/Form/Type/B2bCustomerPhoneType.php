<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class B2bCustomerPhoneType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // basic plain fields
        $builder
            ->add('phone', 'text', array('required' => true))
            ->add('primary', 'checkbox', array('required' => true));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'OroCRM\Bundle\SalesBundle\Entity\B2bCustomerPhone',
                'intention' => 'b2bcustomer',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'cascade_validation' => true,
                'csrf_protection' => false,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_b2bcustomer_phone';
    }
}
