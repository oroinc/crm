<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\SalesBundle\Entity\Lead;

class LeadType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => true, 'label' => 'orocrm.sales.lead.name.label'))
            ->add(
                'status',
                'oro_enum_select',
                [
                    'required'    => false,
                    'label'       => 'orocrm.sales.lead.status.label',
                    'enum_code'   => Lead::INTERNAL_STATUS_CODE
                ]
            )
            ->add(
                'dataChannel',
                'orocrm_channel_select_type',
                array(
                    'required' => true,
                    'label' => 'orocrm.sales.lead.data_channel.label',
                    'entities' => [
                        'OroCRM\\Bundle\\SalesBundle\\Entity\\Lead'
                    ],
                )
            )
            ->add('namePrefix', 'text', array('required' => false, 'label' => 'orocrm.sales.lead.name_prefix.label'))
            ->add('firstName', 'text', array('required' => false, 'label' => 'orocrm.sales.lead.first_name.label'))
            ->add('middleName', 'text', array('required' => false, 'label' => 'orocrm.sales.lead.middle_name.label'))
            ->add('lastName', 'text', array('required' => false, 'label' => 'orocrm.sales.lead.last_name.label'))
            ->add('nameSuffix', 'text', array('required' => false, 'label' => 'orocrm.sales.lead.name_suffix.label'))
            ->add(
                'contact',
                'orocrm_contact_select',
                array(
                    'required' => false,
                    'label' => 'orocrm.sales.lead.contact.label'
                )
            )
            ->add('jobTitle', 'text', array('required' => false, 'label' => 'orocrm.sales.lead.job_title.label'))
            ->add('phoneNumber', 'text', array('required' => false, 'label' => 'orocrm.sales.lead.phone_number.label'))
            ->add('email', 'email', array('required' => false, 'label' => 'orocrm.sales.lead.email.label'))
            ->add(
                'customer',
                'orocrm_sales_b2bcustomer_select',
                array('required' => false, 'label' => 'orocrm.sales.lead.customer.label')
            )
            ->add('companyName', 'text', array('required' => false, 'label' => 'orocrm.sales.lead.company_name.label'))
            ->add('website', 'url', array('required' => false, 'label' => 'orocrm.sales.lead.website.label'))
            ->add(
                'numberOfEmployees',
                'number',
                array(
                    'required' => false,
                    'label' => 'orocrm.sales.lead.number_of_employees.label'
                )
            )
            ->add('industry', 'text', array('required' => false, 'label' => 'orocrm.sales.lead.industry.label'))
            ->add('address', 'oro_address', array('required' => false))
            ->add(
                'source',
                'oro_enum_select',
                array(
                    'required' => false,
                    'label'    => 'orocrm.sales.lead.source.label',
                    'enum_code' => 'lead_source'
                )
            )
            ->add(
                'notes',
                'oro_resizeable_rich_text',
                array(
                    'required' => false,
                    'label' => 'orocrm.sales.lead.notes.label'
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'OroCRM\Bundle\SalesBundle\Entity\Lead',
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
