<?php

namespace Oro\Bundle\ContactBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Oro\Bundle\ContactBundle\Entity\Contact;

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
            ->add('namePrefix', 'text', array('required' => false, 'label' => 'oro.contact.name_prefix.label'))
            ->add('firstName', 'text', array('required' => false, 'label' => 'oro.contact.first_name.label'))
            ->add('middleName', 'text', array('required' => false, 'label' => 'oro.contact.middle_name.label'))
            ->add('lastName', 'text', array('required' => false, 'label' => 'oro.contact.last_name.label'))
            ->add('nameSuffix', 'text', array('required' => false, 'label' => 'oro.contact.name_suffix.label'))
            ->add('gender', 'oro_gender', array('required' => false, 'label' => 'oro.contact.gender.label'))
            ->add('birthday', 'oro_date', array('required' => false, 'label' => 'oro.contact.birthday.label'))
            ->add(
                'description',
                'oro_resizeable_rich_text',
                array(
                    'required' => false,
                    'label' => 'oro.contact.description.label'
                )
            );

        $builder
            ->add('jobTitle', 'text', array('required' => false, 'label' => 'oro.contact.job_title.label'))
            ->add('fax', 'text', array('required' => false, 'label' => 'oro.contact.fax.label'))
            ->add('skype', 'text', array('required' => false, 'label' => 'oro.contact.skype.label'));

        $builder
            ->add('twitter', 'text', array('required' => false, 'label' => 'oro.contact.twitter.label'))
            ->add('facebook', 'text', array('required' => false, 'label' => 'oro.contact.facebook.label'))
            ->add('googlePlus', 'text', array('required' => false, 'label' => 'oro.contact.google_plus.label'))
            ->add('linkedIn', 'text', array('required' => false, 'label' => 'oro.contact.linked_in.label'))
            ->add(
                'picture',
                'oro_image',
                array(
                    'label'          => 'oro.contact.picture.label',
                    'required'       => false
                )
            );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildRelationFields(FormBuilderInterface $builder, array $options)
    {
        // contact source
        $builder->add(
            'source',
            'translatable_entity',
            array(
                'label'       => 'oro.contact.source.label',
                'class'       => 'OroContactBundle:Source',
                'property'    => 'label',
                'required'    => false,
                'empty_value' => false,
            )
        );

        // assigned to (user)
        $builder->add(
            'assignedTo',
            'oro_user_organization_acl_select',
            array('required' => false, 'label' => 'oro.contact.assigned_to.label')
        );

        // reports to (contact)
        $builder->add(
            'reportsTo',
            'oro_contact_select',
            array('required' => false, 'label' => 'oro.contact.reports_to.label')
        );

        // contact method
        $builder->add(
            'method',
            'translatable_entity',
            array(
                'label'       => 'oro.contact.method.label',
                'class'       => 'OroContactBundle:Method',
                'property'    => 'label',
                'required'    => false,
                'empty_value' => 'oro.contact.form.choose_contact_method'
            )
        );

        // addresses, emails and phones
        $builder->add(
            'addresses',
            'oro_address_collection',
            array(
                'label'    => '',
                'type'     => 'oro_typed_address',
                'required' => true,
                'options'  => array('data_class' => 'Oro\Bundle\ContactBundle\Entity\ContactAddress')
            )
        );
        $builder->add(
            'emails',
            'oro_email_collection',
            array(
                'label'    => 'oro.contact.emails.label',
                'type'     => 'oro_email',
                'required' => false,
                'options'  => array('data_class' => 'Oro\Bundle\ContactBundle\Entity\ContactEmail')
            )
        );
        $builder->add(
            'phones',
            'oro_phone_collection',
            array(
                'label'    => 'oro.contact.phones.label',
                'type'     => 'oro_phone',
                'required' => false,
                'options'  => array('data_class' => 'Oro\Bundle\ContactBundle\Entity\ContactPhone')
            )
        );

        // groups
        $builder->add(
            'groups',
            'entity',
            array(
                'label'    => 'oro.contact.groups.label',
                'class'    => 'OroContactBundle:Group',
                'property' => 'label',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'translatable_options' => false
            )
        );

        // accounts
        $builder->add(
            'appendAccounts',
            'oro_entity_identifier',
            array(
                'class'    => 'OroAccountBundle:Account',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            )
        );
        $builder->add(
            'removeAccounts',
            'oro_entity_identifier',
            array(
                'class'    => 'OroAccountBundle:Account',
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
                'data_class'           => 'Oro\Bundle\ContactBundle\Entity\Contact',
                'intention'            => 'contact',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'cascade_validation'   => true,
            )
        );
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var Contact $contact */
        $contact                                       = $form->getData();
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
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_contact';
    }
}
