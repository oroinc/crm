<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
    public function getName()
    {
        return $this->name;
    }
}
