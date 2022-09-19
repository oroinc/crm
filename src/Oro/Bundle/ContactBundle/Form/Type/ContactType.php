<?php

namespace Oro\Bundle\ContactBundle\Form\Type;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\EmailCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\EmailType;
use Oro\Bundle\AddressBundle\Form\Type\PhoneCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\PhoneType;
use Oro\Bundle\AddressBundle\Form\Type\TypedAddressType;
use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\ContactBundle\Entity\Method;
use Oro\Bundle\ContactBundle\Entity\Source;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\FormBundle\Form\Type\OroBirthdayType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Oro\Bundle\UserBundle\Form\Type\GenderType;
use Oro\Bundle\UserBundle\Form\Type\OrganizationUserAclSelectType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for Contact entity
 */
class ContactType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildPlainFields($builder);
        $this->buildRelationFields($builder);

        // set predefined accounts in case of creating a new contact
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $contact = $event->getData();
            if ($contact instanceof Contact
                && !$contact->getId()
                && $contact->hasAccounts()
            ) {
                $form = $event->getForm();
                $form->get('appendAccounts')->setData($contact->getAccounts());
            }
        });
    }

    private function buildPlainFields(FormBuilderInterface $builder): void
    {
        $builder
            ->add('namePrefix', TextType::class, ['required' => false, 'label' => 'oro.contact.name_prefix.label'])
            ->add('firstName', TextType::class, ['required' => false, 'label' => 'oro.contact.first_name.label'])
            ->add('middleName', TextType::class, ['required' => false, 'label' => 'oro.contact.middle_name.label'])
            ->add('lastName', TextType::class, ['required' => false, 'label' => 'oro.contact.last_name.label'])
            ->add('nameSuffix', TextType::class, ['required' => false, 'label' => 'oro.contact.name_suffix.label'])
            ->add('gender', GenderType::class, ['required' => false, 'label' => 'oro.contact.gender.label'])
            ->add('birthday', OroBirthdayType::class, ['required' => false, 'label' => 'oro.contact.birthday.label'])
            ->add(
                'description',
                OroResizeableRichTextType::class,
                ['required' => false, 'label' => 'oro.contact.description.label']
            )
            ->add('jobTitle', TextType::class, ['required' => false, 'label' => 'oro.contact.job_title.label'])
            ->add('fax', TextType::class, ['required' => false, 'label' => 'oro.contact.fax.label'])
            ->add('skype', TextType::class, ['required' => false, 'label' => 'oro.contact.skype.label'])
            ->add('twitter', TextType::class, ['required' => false, 'label' => 'oro.contact.twitter.label'])
            ->add('facebook', TextType::class, ['required' => false, 'label' => 'oro.contact.facebook.label'])
            ->add('googlePlus', TextType::class, ['required' => false, 'label' => 'oro.contact.google_plus.label'])
            ->add('linkedIn', TextType::class, ['required' => false, 'label' => 'oro.contact.linked_in.label'])
            ->add('picture', ImageType::class, ['required' => false, 'label' => 'oro.contact.picture.label']);
    }

    private function buildRelationFields(FormBuilderInterface $builder): void
    {
        $builder->add(
            'source',
            TranslatableEntityType::class,
            [
                'class' => Source::class,
                'required' => false,
                'label' => 'oro.contact.source.label',
                'choice_label' => 'label',
                'placeholder' => false,
            ]
        );
        $builder->add(
            'assignedTo',
            OrganizationUserAclSelectType::class,
            ['required' => false, 'label' => 'oro.contact.assigned_to.label']
        );
        $builder->add(
            'reportsTo',
            ContactSelectType::class,
            ['required' => false, 'label' => 'oro.contact.reports_to.label']
        );
        $builder->add(
            'method',
            TranslatableEntityType::class,
            [
                'class' => Method::class,
                'label' => 'oro.contact.method.label',
                'choice_label' => 'label',
                'required' => false,
                'placeholder' => 'oro.contact.form.choose_contact_method'
            ]
        );
        $builder->add(
            'addresses',
            AddressCollectionType::class,
            [
                'label' => '',
                'entry_type' => TypedAddressType::class,
                'required' => true,
                'entry_options' => ['data_class' => ContactAddress::class]
            ]
        );
        $builder->add(
            'emails',
            EmailCollectionType::class,
            [
                'label' => 'oro.contact.emails.label',
                'entry_type' => EmailType::class,
                'required' => false,
                'entry_options' => ['data_class' => ContactEmail::class]
            ]
        );
        $builder->add(
            'phones',
            PhoneCollectionType::class,
            [
                'label' => 'oro.contact.phones.label',
                'entry_type' => PhoneType::class,
                'required' => false,
                'entry_options' => ['data_class' => ContactPhone::class]
            ]
        );
        $builder->add(
            'groups',
            EntityType::class,
            [
                'label' => 'oro.contact.groups.label',
                'class' => Group::class,
                'choice_label' => 'label',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'translatable_options' => false
            ]
        );
        $builder->add(
            'appendAccounts',
            EntityIdentifierType::class,
            ['class' => Account::class, 'required' => false, 'mapped' => false, 'multiple' => true]
        );
        $builder->add(
            'removeAccounts',
            EntityIdentifierType::class,
            ['class' => Account::class, 'required' => false, 'mapped' => false, 'multiple' => true]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'csrf_token_id' => 'contact',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var Contact $contact */
        $contact = $form->getData();
        $view->children['reportsTo']->vars['excluded'] = array_merge(
            $view->children['reportsTo']->vars['excluded'],
            [$contact->getId()]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_contact';
    }
}
