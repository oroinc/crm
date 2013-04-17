<?php

namespace Oro\Bundle\UIBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OroDateTimeType extends OroDateType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'years'     => range(date('Y') - 120, date('Y')),
            'format'    => \IntlDateFormatter::SHORT,
            'widget'    => 'single_text',
            'attr'      => array(
                'class' => 'datetimepicker',
                'placeholder' => 'MM/DD/YY HH:MM am/pm',
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'datetime';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_datetime';
    }
}
