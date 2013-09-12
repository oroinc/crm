<?php

namespace OroCRM\Bundle\AccountBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Symfony\Component\Routing\Router;

class AccountType extends FlexibleType
{
    /**
     * @var Router
     */
    protected $router;

    public function __construct(FlexibleManager $flexibleManager, $valueFormAlias, Router $router)
    {
        parent::__construct($flexibleManager, $valueFormAlias);
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // add default flexible fields
        parent::addEntityFields($builder);

        // name
        $builder->add(
            'name',
            'text',
            array(
                'label' => 'Name',
                'required' => true,
            )
        );

        // tags
        $builder->add(
            'tags',
            'oro_tag_select'
        );

        // contacts
        $builder->add(
            'contacts',
            'oro_multiple_entity',
            array(
                'class' => 'OroCRMContactBundle:Contact',
                'required' => false
            )
        );

        // addresses
        $builder
            ->add(
                'shippingAddress',
                'oro_address',
                array(
                    'cascade_validation' => true,
                    'required' => false
                )
            )
            ->add(
                'billingAddress',
                'oro_address',
                array(
                    'cascade_validation' => true,
                    'required' => false
                )
            );
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->children['contacts']->vars['grid_url'] =
            $this->router->generate('orocrm_account_contact_select', array('id' => $form->getData()->getId()));
    }

    /**
     * Add entity fields to form builder
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function addDynamicAttributesFields(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'values',
            'collection',
            array(
                'type'         => $this->valueFormAlias,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr'          => array(
                    'data-col'  => 2,
                ),
                'cascade_validation' => true
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
                'cascade_validation' => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_account';
    }
}
