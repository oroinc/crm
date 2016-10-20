<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Form;

use Oro\Bundle\ChannelBundle\Tests\Unit\Form\Extension\IntegrationTypeExtensionTest;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IntegrationFormTypeStub extends AbstractType
{
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'oro_integration_channel_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', 'choice', [
            'choices' => IntegrationTypeExtensionTest::$allChoices,
            'choices_as_values' => true
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Oro\\Bundle\\IntegrationBundle\\Entity\\Channel',
            ]
        );
    }
}
