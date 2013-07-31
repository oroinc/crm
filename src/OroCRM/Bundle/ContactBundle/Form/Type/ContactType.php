<?php

namespace OroCRM\Bundle\ContactBundle\Form\Type;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\AddressBundle\Form\EventListener\AddressCollectionTypeSubscriber;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;

class ContactType extends FlexibleType
{
    /**
     * @var string
     */
    protected $addressClass;

    /**
     * @param FlexibleManager $flexibleManager
     * @param string $valueFormAlias
     * @param string $addressClass
     */
    public function __construct(FlexibleManager $flexibleManager, $valueFormAlias, $addressClass)
    {
        parent::__construct($flexibleManager, $valueFormAlias);
        $this->addressClass = $addressClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber(
            new AddressCollectionTypeSubscriber('addresses', $this->addressClass)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // add default flexible fields
        parent::addEntityFields($builder);

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
