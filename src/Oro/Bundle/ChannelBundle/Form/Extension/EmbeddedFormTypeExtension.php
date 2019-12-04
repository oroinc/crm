<?php

namespace Oro\Bundle\ChannelBundle\Form\Extension;

use Oro\Bundle\EmbeddedFormBundle\Form\Type\EmbeddedFormType;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmbeddedFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [EmbeddedFormType::class];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $modifier = function (FormEvent $event) {
            $form = $event->getForm();

            if ($form->has('additional') && $form->get('additional')->has('dataChannel')) {
                FormUtils::replaceField(
                    $form->get('additional'),
                    'dataChannel',
                    ['required' => true, 'constraints' => [new NotBlank()]]
                );
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $modifier);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $modifier);
    }
}
