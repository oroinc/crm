<?php

namespace OroCRM\Bundle\ContactBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildPlainFields($builder, $options);
        $this->buildRelationFields($builder, $options);

        // set predefined accounts in case of creating a new contact
        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                $contact = $event->getData();
                if ($contact && $contact instanceof Contact && !$contact->getId() && $contact->hasAccounts()) {
                    $form = $event->getForm();
                    $form->get('appendAccounts')->setData($contact->getAccounts());
                }
            }
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
            ->add('birthday', 'oro_date', array('required' => false))
            ->add('description', 'textarea', array('required' => false));

        $builder
            ->add('jobTitle', 'text', array('required' => false))
            ->add('fax', 'text', array('required' => false))
            ->add('skype', 'text', array('required' => false));

        $builder
            ->add('twitter', 'text', array('required' => false))
            ->add('facebook', 'text', array('required' => false))
            ->add('googlePlus', 'text', array('required' => false))
            ->add('linkedIn', 'text', array('required' => false));
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

        // assigned to (user)
        $builder->add('assignedTo', 'oro_user_select', array('required' => false));

        // reports to (contact)
        $builder->add('reportsTo', 'orocrm_contact_select', array('required' => false));

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
        $builder->add('tags', 'oro_tag_select');

        // addresses, emails and phones
        $builder->add(
            'addresses',
            'oro_address_collection',
            array(
                'type' => 'oro_typed_address',
                'required' => true,
                'options' => array('data_class' => 'OroCRM\Bundle\ContactBundle\Entity\ContactAddress')
            )
        )->add(
            'emails',
            'oro_email_collection',
            array(
                'type' => 'oro_email',
                'required' => false,
                'options' => array('data_class' => 'OroCRM\Bundle\ContactBundle\Entity\ContactEmail')
            )
        )->add(
            'phones',
            'oro_phone_collection',
            array(
                'type' => 'oro_phone',
                'required' => false,
                'options' => array('data_class' => 'OroCRM\Bundle\ContactBundle\Entity\ContactPhone')
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
        )->add(
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

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var Contact $contact */
        $contact = $form->getData();
        $view->children['reportsTo']->vars['excluded'] = array_merge(
            $view->children['reportsTo']->vars['excluded'],
            array($contact->getId())
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
