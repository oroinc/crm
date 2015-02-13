<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\TypeTestCase;

use Oro\Bundle\EmbeddedFormBundle\Form\Type\EmbeddedFormInterface;

use OroCRM\Bundle\ContactUsBundle\Form\Type\ContactRequestType;

class ContactRequestTypeTest extends TypeTestCase
{
    /** @var ContactRequestType */
    protected $formType;

    protected function setUp()
    {
        $this->formType = new ContactRequestType();
    }

    protected function tearDown()
    {
        unset($this->formType);
    }

    public function testHasName()
    {
        $this->assertEquals('orocrm_contactus_contact_request', $this->formType->getName());
    }

    public function testParent()
    {
        $this->assertEquals('form', $this->formType->getParent());
    }

    public function testImplementEmbeddedFormInterface()
    {
        $this->assertTrue($this->formType instanceof EmbeddedFormInterface);

        $this->assertNotEmpty($this->formType->getDefaultCss());
        $this->assertInternalType('string', $this->formType->getDefaultCss());

        $this->assertNotEmpty($this->formType->getDefaultSuccessMessage());
        $this->assertInternalType('string', $this->formType->getDefaultSuccessMessage());
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

                        return new \PHPUnit_Framework_MockObject_Stub_ReturnSelf();
                    }
                )
            );

        $this->formType->buildForm($builder, ['dataChannelField' => true]);

        $this->assertSame(
            [
                'dataChannel'  => 'orocrm_channel_select_type',
                'firstName'    => 'text',
                'lastName'     => 'text',
                'emailAddress' => 'text',
                'phone'        => 'text',
                'comment'      => 'textarea',
                'submit'       => 'submit',
            ],
            $fields
        );
    }
}
