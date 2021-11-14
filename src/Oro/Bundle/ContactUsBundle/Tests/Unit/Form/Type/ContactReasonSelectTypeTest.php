<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ContactUsBundle\Form\Type\ContactReasonSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactReasonSelectTypeTest extends TypeTestCase
{
    /** @var ContactReasonSelectType */
    private $formType;

    protected function setUp(): void
    {
        $this->formType = new ContactReasonSelectType();
    }

    public function testGetParent()
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::class, $this->formType->getParent());
    }

    public function testGetBlockPrefix()
    {
        $this->assertEquals('oro_contactus_contact_reason_select', $this->formType->getBlockPrefix());
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'))
            ->willReturnCallback(function (array $options) {
                $this->assertArrayHasKey('autocomplete_alias', $options);
                $this->assertArrayHasKey('create_form_route', $options);
                $this->assertArrayHasKey('configs', $options);
                $this->assertEquals('contact_reasons', $options['autocomplete_alias']);
                $this->assertEquals('oro_contactus_reason_create', $options['create_form_route']);
                $this->assertEquals(
                    [
                        'placeholder' => 'oro.contactus.form.choose_contact_reason'
                    ],
                    $options['configs']
                );
            });

        $this->formType->configureOptions($resolver);
    }
}
