<?php

namespace OroCRM\Bundle\ContactBundle\Form\Type;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\AddressBundle\Form\EventListener\AddressCollectionTypeSubscriber;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;

class ContactType extends FlexibleType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber(
            new AddressCollectionTypeSubscriber('multiAddress', 'OroCRM\Bundle\ContactBundle\Entity\ContactAddress')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // add default flexible fields
        parent::addEntityFields($builder);

        // Addresses
        $builder->add(
            'multiAddress',
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
                'property' => 'name',
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
                'data_class' => $this->flexibleClass,
                'intention' => 'account',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'cascade_validation' => true,
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
