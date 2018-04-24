<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestForm extends AbstractType
{
    /** @var string */
    protected $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'entity_class' => 'Test'
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return $this->name;
    }
}
