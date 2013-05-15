<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class EmailType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('data', 'email');
        $builder->add('type', 'choice', array(
            'empty_value'   => 'Choose email type...',
            'empty_data'    => null,
            'choice_list'   => new ChoiceList(array_keys(self::getEmailTypes()), array_values(self::getEmailTypes()))
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'data_class'    => 'Oro\Bundle\FlexibleEntityBundle\Entity\Email'
                )
            );
    }

    public static function getEmailTypes()
    {
        return array(
            1 => 'test'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_email';
    }
}
