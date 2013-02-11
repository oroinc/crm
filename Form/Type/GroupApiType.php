<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupApiType extends GroupType
{
    /**
     * {@inheritdoc}
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
        return 'group';
    }
}