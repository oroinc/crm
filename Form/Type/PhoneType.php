<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class PhoneType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('phone', 'text');
        $builder->add('area_code', 'text');
        $builder->add('type', 'choice', array(
            'empty_value'   => 'Choose type...',
            'empty_data'    => null,
            'choice_list'   => new ChoiceList(array_keys(self::getPhoneTypes()), array_values(self::getPhoneTypes()))
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
                    'data_class'    => 'Oro\Bundle\FlexibleEntityBundle\Entity\Phone'
                )
            );
    }

    public static function getPhoneTypes()
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
        return 'oro_flexibleentity_phone';
    }
}
