<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\ContactUsBundle\Form\Type\ContactRequestType;
use Oro\Bundle\EmbeddedFormBundle\Form\Type\EmbeddedFormInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\TypeTestCase;

class ContactRequestTypeTest extends TypeTestCase
{
    /** @var ContactRequestType */
    protected $formType;

    protected function setUp(): void
    {
        $this->formType = new ContactRequestType();
    }

    protected function tearDown(): void
    {
        unset($this->formType);
    }

    public function testHasName()
    {
        $this->assertEquals('oro_contactus_contact_request', $this->formType->getName());
    }

    public function testImplementEmbeddedFormInterface()
    {
        $this->assertTrue($this->formType instanceof EmbeddedFormInterface);

        $this->assertNotEmpty($this->formType->getDefaultCss());
        $this->assertIsString($this->formType->getDefaultCss());

        $this->assertNotEmpty($this->formType->getDefaultSuccessMessage());
        $this->assertIsString($this->formType->getDefaultSuccessMessage());
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()->getMock();

        $fields = [];
        $builder->expects($this->exactly(7))
            ->method('add')
            ->will(
                $this->returnCallback(
                    function ($fieldName, $fieldType) use (&$fields) {
                        $fields[$fieldName] = $fieldType;

                        return new \PHPUnit\Framework\MockObject\Stub\ReturnSelf();
                    }
                )
            );

        $this->formType->buildForm($builder, ['dataChannelField' => true]);

        $this->assertSame(
            [
                'dataChannel'  => ChannelSelectType::class,
                'firstName'    => TextType::class,
                'lastName'     => TextType::class,
                'emailAddress' => TextType::class,
                'phone'        => TextType::class,
                'comment'      => TextareaType::class,
                'submit'       => SubmitType::class,
            ],
            $fields
        );
    }
}
