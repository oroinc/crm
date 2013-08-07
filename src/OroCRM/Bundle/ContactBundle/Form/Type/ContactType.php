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
        $this->buildPlainFields($builder, $options);
        $this->buildRelationFields($builder, $options);

        $builder->addEventSubscriber(
            new AddressCollectionTypeSubscriber('addresses', 'OroCRM\Bundle\ContactBundle\Entity\ContactAddress')
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    protected function buildPlainFields(FormBuilderInterface $builder, array $options)
    {
        // basic plain fields
        $builder
            ->add('namePrefix', 'text', array('required' => false))
            ->add('firstName', 'text', array('required' => true))
            ->add('lastName', 'text', array('required' => true))
            ->add('nameSuffix', 'text', array('required' => false))
            ->add('gender', 'oro_gender', array('required' => false))
            ->add('title', 'text', array('required' => false))
            ->add('birthday', 'oro_date', array('required' => false))
            ->add('description', 'textarea', array('required' => false));

        $builder
            ->add('jobTitle', 'text', array('required' => false))
            ->add('fax', 'text', array('required' => false))
            ->add('skype', 'text', array('required' => false));

        $builder
            ->add('twitterUrl', 'text', array('required' => false))
            ->add('facebookUrl', 'text', array('required' => false))
            ->add('googlePlusUrl', 'text', array('required' => false))
            ->add('linkedInUrl', 'text', array('required' => false));
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildRelationFields(FormBuilderInterface $builder, array $options)
    {
        // contact source
        $builder->add(
            'source',
            'entity',
            array(
                'class'       => 'OroCRMContactBundle:Source',
                'property'    => 'label',
                'required'    => false,
                'empty_value' => false,
            )
        );

        // owner and assigned to (users)
        $builder->add('owner', 'oro_user_select', array('required' => false));
        $builder->add('assignedTo', 'oro_user_select', array('required' => false));

        // reports to (contact)
        $builder->add('reportsTo', 'orocrm_contact_select', array('required' => false));

        // email and phone
        // TODO Implement as collections with primary item
        $builder
            ->add('email', 'email', array('required' => false))
            ->add('phone', 'text', array('required' => false));

        // contact method
        $builder->add(
            'method',
            'entity',
            array(
                'class'       => 'OroCRMContactBundle:Method',
                'property'    => 'label',
                'required'    => false,
                'empty_value' => 'orocrm.contact.form.choose_contact_method'
            )
        );

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
