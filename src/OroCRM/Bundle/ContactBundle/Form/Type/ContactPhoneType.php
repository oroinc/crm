<?php

namespace OroCRM\Bundle\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

class ContactPhoneType extends AbstractType
{
    const CONTACT_OPTION_KEY = 'contact_field';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->setAttribute(self::CONTACT_OPTION_KEY, $options[self::CONTACT_OPTION_KEY]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = function (Options $options) {
            // show empty list if contact is not selected
            if (empty($options['contact'])) {
                return array();
            }

            return null;
        };

        $resolver
            ->setDefaults(
                array(
                    'class'         => 'OroCRMContactBundle:ContactPhone',
                    'property'      => 'phone',
                    'query_builder' => null,
                    'choices'       => $choices,
                    'contact'       => null,
                    'contact_field' => null,
                    'configs' => array(
                        'placeholder' => 'orocrm.contact.form.choose_phone',
                    ),
                    'empty_value' => '',
                    'empty_data'  => null
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['contact_field'] = $form->getConfig()->getAttribute(self::CONTACT_OPTION_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_contact_phone';
    }
}
