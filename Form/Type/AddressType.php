<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\EventListener\BuildAddressFormListener;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;

class AddressType extends FlexibleType
{
    /**
     * @var BuildAddressFormListener
     */
    private $eventListener;

    /**
     * @param FlexibleManager $flexibleManager
     * @param string $valueFormAlias
     * @param BuildAddressFormListener $eventListener
     */
    public function __construct(FlexibleManager $flexibleManager, $valueFormAlias, BuildAddressFormListener $eventListener)
    {
        $this->eventListener = $eventListener;
        parent::__construct($flexibleManager, $valueFormAlias);
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // add default flexible fields
        parent::addEntityFields($builder);

        $builder->addEventSubscriber($this->eventListener);

        $required =  array(
            'required' => true,
        );
        $notRequired =  array(
            'required' => false,
        );

        // address fields
        $builder
            ->add('firstName', 'text', $required)
            ->add('lastName', 'text', $required)
            ->add('street', 'text', $required)
            ->add('street2', 'text', $notRequired)
            ->add('city', 'text', $required)
            ->add('state', 'oro_region', $notRequired)
            ->add('state_text', 'hidden', $notRequired)
            ->add('country', 'oro_country', $required)
            ->add('postalCode', 'text', array('required' => true, 'label' => 'ZIP/Postal code'));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'           => $this->flexibleClass,
                'intention'            => 'address',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_address';
    }
}
