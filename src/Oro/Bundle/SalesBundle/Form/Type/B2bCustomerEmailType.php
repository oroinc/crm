<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class B2bCustomerEmailType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // basic plain fields
        $builder
            ->add('email', 'text', array('required' => true))
            ->add('primary', 'checkbox', array('required' => true));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail',
                'intention' => 'b2bcustomer',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'csrf_protection' => false
            )
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
        return 'oro_b2bcustomer_email';
    }
}
