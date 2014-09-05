<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\PreloadedExtension;
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

        $this->assertNotEmpty($this->formType->getFormLayout());
        $this->assertInternalType('string', $this->formType->getFormLayout());
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(7))
            ->method('add')
            ->will(
                $this->returnValueMap(
                    [
                        [['dataChannel', 'orocrm_channel_select_type'], $this->returnSelf()],
                        [['firstName', 'text'], $this->returnSelf()],
                        [['lastName', 'text'], $this->returnSelf()],
                        [['organizationName', 'text'], $this->returnSelf()],
                        [['preferredContactMethod', 'choice'], $this->returnSelf()],
                        [['phone', 'text'], $this->returnSelf()],
                        [['emailAddress', 'text'], $this->returnSelf()],
                        [['contactReason', 'entity'], $this->returnSelf()],
                        [['comment', 'textarea'], $this->returnSelf()],
                        [['submit', 'submit'], $this->returnSelf()],
                    ]
                )
            );
        $this->formType->buildForm($builder, []);
    }
}
