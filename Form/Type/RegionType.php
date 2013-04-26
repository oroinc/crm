<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $list = function (Options $options) {
            if (null === $options['country']) {
                return new ObjectChoiceList(array());
            }

            return new ObjectChoiceList($options['country']->getRegions());
        };

        $resolver
            ->setDefaults(
                array(
                    'choice_list' => $list,
                    'country'     => null,
                    'empty_value' => 'Choose a state...',
                    'empty_data'  => null
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_region';
    }
}
