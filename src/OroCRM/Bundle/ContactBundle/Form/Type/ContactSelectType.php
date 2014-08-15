<?php
namespace OroCRM\Bundle\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactSelectType extends AbstractType
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'configs'      => [
                    'placeholder'             => 'orocrm.contact.form.choose_contact',
                    'result_template_twig'    => 'OroFormBundle:Autocomplete:fullName/result.html.twig',
                    'selection_template_twig' => 'OroFormBundle:Autocomplete:fullName/selection.html.twig'
                ],
                'entity_class' => $this->className,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_entity_create_or_select_inline';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_contact_select';
    }
}
