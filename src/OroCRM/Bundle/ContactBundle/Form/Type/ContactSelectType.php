<?php
namespace OroCRM\Bundle\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactSelectType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => 'orocrm.contact.form.choose_contact',
                    'result_template_twig' => 'OroFormBundle:Autocomplete:fullName/result.html.twig',
                    'selection_template_twig' => 'OroFormBundle:Autocomplete:fullName/selection.html.twig'
                ),
                'autocomplete_alias' => 'contacts'
            )
        );
    }

    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_contact_select';
    }
}
