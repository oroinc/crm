<?php

namespace Oro\Bundle\ContactBundle\Form\Type;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for contact group REST API operations.
 *
 * Extends the standard contact group form type to support REST API requests by adding
 * PATCH subscriber functionality. This enables proper handling of partial updates where
 * unset fields are not overwritten with null values, allowing clients to update only
 * specific fields of a contact group via REST API calls.
 */
class GroupApiType extends GroupType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber(new PatchSubscriber());
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\ContactBundle\Entity\Group',
                'csrf_token_id' => 'group',
                'csrf_protection' => false,
            )
        );
    }

    #[\Override]
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'contact_group';
    }
}
