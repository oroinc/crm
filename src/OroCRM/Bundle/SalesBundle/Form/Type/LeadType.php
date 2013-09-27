<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeadType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => true))
            ->add('firstName', 'text', array('required' => true))
            ->add('lastName', 'text', array('required' => true))
            ->add('contact', 'orocrm_contact_select', array('required' => false))
            ->add('jobTitle', 'text', array('required' => false))
            ->add('phoneNumber', 'text', array('required' => false))
            ->add('email', 'email', array('required' => false))
            ->add('companyName', 'text', array('required' => false))
            ->add('website', 'url', array('required' => false))
            ->add('numberOfEmployees', 'number', array('required' => false))
            ->add('industry', 'text', array('required' => false))
            ->add('address', 'orocrm_lead_address', array('required' => false));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'OroCRM\Bundle\SalesBundle\Entity\Lead',
                'cascade_validation' => true,
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'orocrm_sales_lead';
    }
}
