<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\EmailCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\EmailType;
use Oro\Bundle\AddressBundle\Form\Type\PhoneCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\PhoneType;
use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Lead entity form type.
 * - used in backoffice on create, update entity pages.
 * - used in API as parent form type for LeadApiType
 */
class LeadType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array('required' => true, 'label' => 'oro.sales.lead.name.label'))
            ->add(
                'status',
                EnumSelectType::class,
                [
                    'required'    => false,
                    'label'       => 'oro.sales.lead.status.label',
                    'enum_code'   => Lead::INTERNAL_STATUS_CODE
                ]
            )
            ->add('namePrefix', TextType::class, ['required' => false, 'label' => 'oro.sales.lead.name_prefix.label'])
            ->add('firstName', TextType::class, ['required' => false, 'label' => 'oro.sales.lead.first_name.label'])
            ->add('middleName', TextType::class, ['required' => false, 'label' => 'oro.sales.lead.middle_name.label'])
            ->add('lastName', TextType::class, ['required' => false, 'label' => 'oro.sales.lead.last_name.label'])
            ->add('nameSuffix', TextType::class, ['required' => false, 'label' => 'oro.sales.lead.name_suffix.label'])
            ->add(
                'contact',
                ContactSelectType::class,
                array(
                    'required' => false,
                    'label' => 'oro.sales.lead.contact.label'
                )
            )
            ->add('jobTitle', TextType::class, array('required' => false, 'label' => 'oro.sales.lead.job_title.label'))
            ->add(
                'phones',
                PhoneCollectionType::class,
                array(
                    'label' => 'oro.sales.lead.phones.label',
                    'entry_type' => PhoneType::class,
                    'required' => false,
                    'entry_options' => array('data_class' => 'Oro\Bundle\SalesBundle\Entity\LeadPhone')
                )
            )
            ->add(
                'emails',
                EmailCollectionType::class,
                array(
                    'label'    => 'oro.sales.lead.emails.label',
                    'entry_type'     => EmailType::class,
                    'required' => false,
                    'entry_options'  => array('data_class' => 'Oro\Bundle\SalesBundle\Entity\LeadEmail')
                )
            )
            ->add(
                'customerAssociation',
                CustomerType::class,
                [
                    'required'     => false,
                    'label'        => 'oro.sales.lead.customer.label',
                    'parent_class' => $options['data_class'],
                ]
            )
            ->add('companyName', TextType::class, ['required' => false, 'label' => 'oro.sales.lead.company_name.label'])
            ->add('website', TextType::class, array('required' => false, 'label' => 'oro.sales.lead.website.label'))
            ->add(
                'numberOfEmployees',
                IntegerType::class,
                array(
                    'required' => false,
                    'label' => 'oro.sales.lead.number_of_employees.label'
                )
            )
            ->add('industry', TextType::class, array('required' => false, 'label' => 'oro.sales.lead.industry.label'))
            ->add(
                'addresses',
                AddressCollectionType::class,
                [
                    'label'    => '',
                    'entry_type'     => LeadAddressType::class,
                    'required' => false,
                    'add_label'  => 'oro.sales.lead.add_address.label',
                    'show_form_when_empty' => true,
                    'block_name' => 'address_collection',
                    'entry_options'  => [
                        'data_class' => 'Oro\Bundle\SalesBundle\Entity\LeadAddress',
                    ]
                ]
            )
            ->add(
                'source',
                EnumSelectType::class,
                array(
                    'required' => false,
                    'label'    => 'oro.sales.lead.source.label',
                    'enum_code' => 'lead_source'
                )
            )
            ->add(
                'notes',
                OroResizeableRichTextType::class,
                array(
                    'required' => false,
                    'label' => 'oro.sales.lead.notes.label'
                )
            )
            ->add('twitter', TextType::class, array('required' => false, 'label' => 'oro.sales.lead.twitter.label'))
            ->add('linkedIn', TextType::class, array('required' => false, 'label' => 'oro.sales.lead.linked_in.label'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\SalesBundle\Entity\Lead',
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
