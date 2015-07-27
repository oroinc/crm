<?php

namespace OroCRM\Bundle\CallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

class CallApiType extends AbstractType
{
    const NAME = 'orocrm_call_form_api';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Add a hidden field to pass form validation
        $builder->add(
            'associations',
            'hidden',
            [
                'mapped'      => false,
                'constraints' => [
                    new Callback([[$this, 'validateAssociations']])
                ]
            ]
        );

        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'orocrm_call_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param array                     $associations
     * @param ExecutionContextInterface $context
     */
    public function validateAssociations($associations, ExecutionContextInterface $context)
    {
        if (empty($associations)) {
            return;
        }

        foreach ($associations as $index => $association) {
            if (empty($association['entityName']) || empty($association['entityId'])) {
                $context->addViolation(
                    'Invalid association provided at position {{index}}. Entity Name and Entity ID should not be null.',
                    [
                        '{{index}}' => $index+1
                    ]
                );
            }
        }
    }
}
