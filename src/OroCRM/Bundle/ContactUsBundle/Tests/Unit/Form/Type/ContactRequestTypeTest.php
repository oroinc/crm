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
        $expectedCss = <<<CSS
ul, li {
     list-style: none;
     overflow: hidden;
     clear: both;
     margin: 0;
 }
.page-title {
    width: 100%;
    overflow: hidden;
    border-bottom: 1px solid #ccc;
    margin: 0 0 25px;
}
 .page-title h1{
     margin: 0;
     font-size: 20px;
     color: #0a263c;
 }

.fieldset {
    border: 1px solid #bbafa0;
    background: #fbfaf6;
    padding: 22px 25px 12px 33px;
    margin: 28px 0;
}

.fieldset .legend {
    float: left;
    font-size: 13px;
    line-height: 20px;
    border: 1px solid #f19900;
    background: #f9f3e3;
    color: #e76200;
    margin: -33px 0 0 -10px;
    padding: 0 8px;
    position: relative;
}

.form-list .control-group {
    margin-bottom: 0px;
}

.form-list li.fields .control-group {
    float: left;
    width: 275px;
}

.form-list label {
    float: left;
    color: #666;
    font-weight: bold;
    position: relative;
    z-index: 0;
    margin-bottom: 0;
}
.form-list .controls {
    display: block;
    clear: both;
    width: 260px;
}

.form-list input[type="text"],
.form-list input[type="email"] {
    width: 254px;
    padding: 3px;
    margin-bottom: 3px;

}

.form-list li.wide .controls {
    width: 550px;
}

.form-list li.wide textarea {
    width: 100%;
    padding: 3px;
    margin-bottom: 3px;
}

.buttons-set {
    clear: both;
    margin: 4em 0 0;
    padding: 8px 10px 0;
    border-top: 1px solid #e4e4e4;
    text-align: right;
}

.buttons-set p.required {
    margin: 0 0 10px;
    font-size: 11px;
    text-align: right;
    color: #EB340A;
}

.buttons-set button {
    float: right;
    margin-left: 5px;
    display: block;
    height: 19px;
    border: 1px solid #de5400;
    background: #f18200;
    padding: 0 8px;
    font: bold 12px/19px Arial, Helvetica, sans-serif;
    text-align: center;
    white-space: nowrap;
    color: #fff;
    box-sizing: content-box;
}
CSS;

        $this->assertEquals($expectedCss, $type->getDefaultCss());
    }
}
