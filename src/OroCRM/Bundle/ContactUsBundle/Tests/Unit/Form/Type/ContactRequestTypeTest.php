<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Unit\Form\Type;


use OroCRM\Bundle\ContactUsBundle\Form\Type\ContactRequestType;

class ContactRequestTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testInterfaceImplementation()
    {
        $rc = new \ReflectionClass('OroCRM\Bundle\ContactUsBundle\Form\Type\ContactRequestType');
        $this->assertTrue($rc->implementsInterface('Oro\Bundle\EmbeddedFormBundle\Form\Type\EmbeddedFormInterface'));
    }

    public function testBuildForm()
    {
        $expectedFields = array(
            'name' => null,
            'email' => null,
            'phone' => null,
            'comment' => 'textarea',
            'submit' => 'submit',
        );

        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $counter = 0;
        foreach ($expectedFields as $fieldName => $formType) {
            $builder->expects($this->at($counter))
                ->method('add')
                ->with($fieldName, $formType)
                ->will($this->returnSelf());
            $counter++;
        }

        $type = new ContactRequestType();
        $type->buildForm($builder, []);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class' => 'OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest',
                ]
            );

        $type = new ContactRequestType();
        $type->setDefaultOptions($resolver);
    }

    public function testParentClass()
    {
        $rc = new \ReflectionClass('OroCRM\Bundle\ContactUsBundle\Form\Type\ContactRequestType');
        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Form\AbstractType'));
    }

    public function testGetParent()
    {
        $type = new ContactRequestType();
        $this->assertEquals('oro_channel_aware_form', $type->getParent());
    }

    public function testGetName()
    {
        $type = new ContactRequestType();
        $this->assertEquals('contact_request', $type->getName());
    }

    public function testDefaultSuccessMessage()
    {
        $type = new ContactRequestType();
        $this->assertEquals(
            '<h3>Form has been submitted successfully</h3>{back_link}',
            $type->getDefaultSuccessMessage()
        );
    }

    public function testDefaultCss()
    {
        $type = new ContactRequestType();
        $result = $type->getDefaultCss();

        $this->assertNotEmpty($result);
        $this->assertInternalType('string', $result);
    }
}
