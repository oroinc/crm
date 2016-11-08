<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SalesBundle\Entity\Lead;

class LeadType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => true, 'label' => 'oro.sales.lead.name.label'))
            ->add(
                'status',
                'oro_enum_select',
                [
                    'required'    => false,
                    'label'       => 'oro.sales.lead.status.label',
                    'enum_code'   => Lead::INTERNAL_STATUS_CODE
                ]
            )
            ->add(
                'dataChannel',
                'oro_channel_select_type',
                array(
                    'required' => true,
                    'label' => 'oro.sales.lead.data_channel.label',
                )
            )
            ->add('namePrefix', 'text', array('required' => false, 'label' => 'oro.sales.lead.name_prefix.label'))
            ->add('firstName', 'text', array('required' => false, 'label' => 'oro.sales.lead.first_name.label'))
            ->add('middleName', 'text', array('required' => false, 'label' => 'oro.sales.lead.middle_name.label'))
            ->add('lastName', 'text', array('required' => false, 'label' => 'oro.sales.lead.last_name.label'))
            ->add('nameSuffix', 'text', array('required' => false, 'label' => 'oro.sales.lead.name_suffix.label'))
            ->add(
                'contact',
                'oro_contact_select',
                array(
                    'required' => false,
                    'label' => 'oro.sales.lead.contact.label'
                )
            )
            ->add('jobTitle', 'text', array('required' => false, 'label' => 'oro.sales.lead.job_title.label'))
            ->add(
                'phones',
                'oro_phone_collection',
                array(
                    'label' => 'oro.sales.lead.phones.label',
                    'type' => 'oro_phone',
                    'required' => false,
                    'options' => array('data_class' => 'Oro\Bundle\SalesBundle\Entity\LeadPhone')
                )
            )
            ->add(
                'emails',
                'oro_email_collection',
                array(
                    'label'    => 'oro.sales.lead.emails.label',
                    'type'     => 'oro_email',
                    'required' => false,
                    'options'  => array('data_class' => 'Oro\Bundle\SalesBundle\Entity\LeadEmail')
                )
            )
            ->add(
                'customer',
                'oro_sales_customer',
                [
                    'required'     => false,
                    'label'        => 'oro.sales.lead.customer.label',
                    'parent_class' => $options['data_class'],
                ]
            )
            ->add('companyName', 'text', array('required' => false, 'label' => 'oro.sales.lead.company_name.label'))
            ->add('website', 'url', array('required' => false, 'label' => 'oro.sales.lead.website.label'))
            ->add(
                'numberOfEmployees',
                'number',
                array(
                    'required' => false,
                    'label' => 'oro.sales.lead.number_of_employees.label'
                )
            )
            ->add('industry', 'text', array('required' => false, 'label' => 'oro.sales.lead.industry.label'))
            ->add(
                'addresses',
                'oro_address_collection',
                [
                    'label'    => '',
                    'type'     => 'oro_sales_lead_address',
                    'required' => false,
                    'add_label'  => 'oro.sales.lead.add_address.label',
                    'show_form_when_empty' => true,
                    'block_name' => 'address_collection',
                    'options'  => [
                        'data_class' => 'Oro\Bundle\SalesBundle\Entity\LeadAddress',
                    ]
                ]
            )
            ->add(
                'source',
                'oro_enum_select',
                array(
                    'required' => false,
                    'label'    => 'oro.sales.lead.source.label',
                    'enum_code' => 'lead_source'
                )
            )
            ->add(
                'notes',
                'oro_resizeable_rich_text',
                array(
                    'required' => false,
                    'label' => 'oro.sales.lead.notes.label'
                )
            )
            ->add('twitter', 'text', array('required' => false, 'label' => 'oro.sales.lead.twitter.label'))
            ->add('linkedIn', 'text', array('required' => false, 'label' => 'oro.sales.lead.linked_in.label'));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Oro\Bundle\SalesBundle\Entity\Lead',
                'cascade_validation' => true,
            )
        );
    }

    /**
     * @return string
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
        return 'oro_sales_lead';
    }
}
