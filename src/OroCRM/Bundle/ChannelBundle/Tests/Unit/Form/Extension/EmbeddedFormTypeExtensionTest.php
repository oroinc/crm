<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\Extension;

use OroCRM\Bundle\ChannelBundle\Form\Extension\EmbeddedFormTypeExtension;

use Symfony\Component\Form\Test\FormIntegrationTestCase;

class EmbeddedFormTypeExtensionTest extends FormIntegrationTestCase
{
    /** @var EmbeddedFormTypeExtension */
    protected $extension;

    protected function setUp()
    {
        $this->extension = new EmbeddedFormTypeExtension();
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->extension);
        parent::tearDown();
    }

    public function testGetExtendedType()
    {
        $this->assertEquals(
            $this->extension->getExtendedType(),
            'embedded_form'
        );
    }

    public function testBuildForm()
    {
        $builder      = $this->factory->createNamedBuilder('root');
        $builderInner = $this->factory->createNamedBuilder('additional');
        $builderInner->add('dataChannel', 'text');
        $builder->add($builderInner);

        $form = $builder->getForm();

        $this->extension->buildForm($builder, []);

        $this->assertTrue($form->get('additional')->get('dataChannel')->getConfig()->getOption('required'));
        $this->assertFalse($form->get('additional')->get('dataChannel')->getConfig()->getOption('error_bubbling'));
    }
}
