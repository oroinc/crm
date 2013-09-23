<?php

namespace OroCRM\Bundle\AccountBundle\Form\Type;

use Doctrine\Common\Collections\Collection;

use Symfony\Component\Routing\Router;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\AccountBundle\Entity\Account;

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
                'label' => 'orocrm.account.form.account_name',
                'required' => true,
            )
        );

        // tags
        $builder->add(
            'tags',
            'oro_tag_select'
        );

        $builder->add(
            'default_contact',
            'oro_entity_identifier',
            array(
                'class'    => 'OroCRMContactBundle:Contact',
                'multiple' => false
            )
        );

        // contacts
        $builder->add(
            'contacts',
            'oro_multiple_entity',
            array(
                'class' => 'OroCRMContactBundle:Contact',
                'required' => false,
                'default_element' => 'default_contact',
                'selector_window_title' => 'orocrm.account.form.select_contacts'
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

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var Account $account */
        $account = $form->getData();
        $view->children['contacts']->vars['grid_url']
            = $this->router->generate('orocrm_account_contact_select', array('id' => $account->getId()));
        $defaultContactId = $account->getDefaultContact() ? $account->getDefaultContact()->getId() : null;
        $view->children['contacts']->vars['initial_elements']
            = $this->getInitialElements($account->getContacts(), $defaultContactId);
    }

    /**
     * @param Collection $contacts
     * @param int|null $default
     * @return array
     */
    protected function getInitialElements(Collection $contacts, $default)
    {
        $result = array();
        /** @var Contact $contact */
        foreach ($contacts as $contact) {
            $primaryPhone = $contact->getPrimaryPhone();
            $primaryEmail = $contact->getPrimaryEmail();
            $result[] = array(
                'id' => $contact->getId(),
                'label' => $contact->getFirstName() . ' ' . $contact->getLastName(),
                'link' => $this->router->generate('orocrm_contact_info', array('id' => $contact->getId())),
                'extraData' => array(
                    array('label' => 'Phone', 'value' => $primaryPhone ? $primaryPhone->getPhone() : null),
                    array('label' => 'Email', 'value' => $primaryEmail ? $primaryEmail->getEmail() : null),
                ),
                'isDefault' => $default == $contact->getId()
            );
        }
        return $result;
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
