<?php

namespace OroCRM\Bundle\ContactBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

use Oro\Bundle\AddressBundle\Form\EventListener\AddressCollectionTypeSubscriber;

class ContactType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // basic plain fields
        $builder
            ->add('namePrefix', 'text', array('label' => 'Name prefix', 'required' => false))
            ->add('firstName', 'text', array('label' => 'First name', 'required' => true))
            ->add('lastName', 'text', array('label' => 'Last name', 'required' => true))
            ->add('nameSuffix', 'text', array('label' => 'Name suffix', 'required' => false))
            ->add('title', 'text', array('label' => 'Title', 'required' => false))
            ->add('birthday', 'oro_date', array('label' => 'Birthday', 'required' => false))
            ->add('description', 'textarea', array('label' => 'Description', 'required' => false));

        // contact source
        $builder->add(
            'source',
            'entity',
            array(
                'class'       => 'OroCRMContactBundle:ContactSource',
                'property'    => 'label',
                'required'    => false,
                'empty_value' => false,
            )
        );

        // assigned to (user)
        $builder->add('assignedTo', 'oro_user_select', array('label' => 'Assigned to', 'required' => false));

        // reports to (contact)
        $builder->add('reportsTo', 'orocrm_contact_select', array('label' => 'Reports to', 'required' => false));

        // email and phone
        // TODO Implement as collections with primary item
        $builder
            ->add('email', 'email', array('label' => 'Email', 'required' => false))
            ->add('phone', 'text', array('label' => 'Phone', 'required' => false));

        // tags
        $builder->add(
            'tags',
            'oro_tag_select'
        );

        // Addresses
        $builder->add(
            'addresses',
            'oro_address_collection',
            array(
                'required' => true,
                'type' => 'orocrm_contact_address',
            )
        );

        // groups
        $builder->add(
            'groups',
            'entity',
            array(
                'class'    => 'OroCRMContactBundle:Group',
                'property' => 'label',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            )
        );

        // accounts
        $builder->add(
            'appendAccounts',
            'oro_entity_identifier',
            array(
                'class'    => 'OroCRMAccountBundle:Account',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            )
        )
        ->add(
            'removeAccounts',
            'oro_entity_identifier',
            array(
                'class'    => 'OroCRMAccountBundle:Account',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            )
        );

        $builder->addEventSubscriber(
            new AddressCollectionTypeSubscriber('addresses', 'OroCRM\Bundle\ContactBundle\Entity\ContactAddress')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'           => 'OroCRM\Bundle\ContactBundle\Entity\Contact',
                'intention'            => 'contact',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'cascade_validation'   => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_contact';
    }
}
