<?php

namespace Oro\Bundle\ChannelBundle\Form\Extension;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Validator\Constraints\NotBlank;

use Oro\Bundle\FormBundle\Utils\FormUtils;

class EmbeddedFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'embedded_form';
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
