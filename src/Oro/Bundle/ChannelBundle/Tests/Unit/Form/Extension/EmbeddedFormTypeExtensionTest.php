<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\ChannelBundle\Form\Extension\EmbeddedFormTypeExtension;
use Oro\Bundle\EmbeddedFormBundle\Form\Type\EmbeddedFormType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmbeddedFormTypeExtensionTest extends FormIntegrationTestCase
{
    private EmbeddedFormTypeExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new EmbeddedFormTypeExtension();
        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [],
                [
                    FormType::class => [
                        new FormTypeValidatorExtension(
                            $this->createMock(ValidatorInterface::class)
                        )
                    ]
                ]
            )
        ];
    }

    public function testGetExtendedTypes(): void
    {
        $this->assertEquals([EmbeddedFormType::class], EmbeddedFormTypeExtension::getExtendedTypes());
    }

    public function testBuildForm()
    {
        $builder = $this->factory->createNamedBuilder('root');
        $builderInner = $this->factory->createNamedBuilder('additional');
        $builderInner->add('dataChannel', TextType::class, ['required' => false, 'constraints' => []]);
        $builder->add($builderInner);

        $form = $builder->getForm();

        $this->extension->buildForm($builder, []);
        $form->setData([]);

        $this->assertTrue($form->get('additional')->get('dataChannel')->getConfig()->getOption('required'));
        $this->assertNotEmpty($form->get('additional')->get('dataChannel')->getConfig()->getOption('constraints'));
    }
}
