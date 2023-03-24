<?php
declare(strict_types=1);

namespace Oro\Bundle\ContactUsBundle\Form\Type;

use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Oro\Bundle\ContactUsBundle\Entity\Repository\ContactReasonRepository;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This form can be used to edit an existing contact request (e.g. in the back-office).
 */
class ContactRequestEditType extends AbstractType
{
    private LocalizationHelper $localizationHelper;

    public function __construct(LocalizationHelper $localizationHelper)
    {
        $this->localizationHelper = $localizationHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('firstName', TextType::class, ['label' => 'oro.contactus.contactrequest.first_name.label']);
        $builder->add('lastName', TextType::class, ['label' => 'oro.contactus.contactrequest.last_name.label']);
        $builder->add(
            'organizationName',
            TextType::class,
            ['required' => false, 'label' => 'oro.contactus.contactrequest.organization_name.label']
        );
        $builder->add(
            'preferredContactMethod',
            ChoiceType::class,
            [
                'choices'  => [
                    ContactRequest::CONTACT_METHOD_BOTH  => ContactRequest::CONTACT_METHOD_BOTH,
                    ContactRequest::CONTACT_METHOD_PHONE => ContactRequest::CONTACT_METHOD_PHONE,
                    ContactRequest::CONTACT_METHOD_EMAIL => ContactRequest::CONTACT_METHOD_EMAIL
                ],
                'required' => true,
                'label'    => 'oro.contactus.contactrequest.preferred_contact_method.label',
            ]
        );
        $builder->add(
            'phone',
            TextType::class,
            ['required' => false, 'label' => 'oro.contactus.contactrequest.phone.label']
        );
        $builder->add(
            'emailAddress',
            TextType::class,
            ['required' => false, 'label' => 'oro.contactus.contactrequest.email_address.label']
        );
        $builder->add(
            'contactReason',
            EntityType::class,
            [
                'class' => ContactReason::class,
                'choice_label' => fn (ContactReason $r) => $this->localizationHelper->getLocalizedValue(
                    $r->getTitles()
                ),
                'placeholder' => 'oro.contactus.contactrequest.choose_contact_reason.label',
                'required' => false,
                'label' => 'oro.contactus.contactrequest.contact_reason.label',
                'client_validation' => false,
                'query_builder' => fn (ContactReasonRepository $er) => $er->createExistingContactReasonsWithTitlesQB(),
            ]
        );
        $builder->add('comment', TextareaType::class, ['label' => 'oro.contactus.contactrequest.comment.label']);
        $builder->add('submit', SubmitType::class);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ContactRequest::class]);
    }
}
