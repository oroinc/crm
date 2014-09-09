<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'error_mapping' => [
                    'dataChannel' => 'additional.dataChannel',
                ],
                'cascade_validation' => true,
            ]
        );
    }
}
