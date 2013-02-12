<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProfileApiType extends ProfileType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'profile';
    }
}
