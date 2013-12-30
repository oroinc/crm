<?php

namespace OroCRM\Bundle\CallBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;

use OroCRM\Bundle\CallBundle\Form\Type\CallType;

class CallTypeTest extends FormIntegrationTestCase
{
    /**
     * @var CallType
     */
    protected $type;

    protected function setUp()
    {
        parent::setUp();

        $contactPhoneSubscriber =
            $this->getMockBuilder('OroCRM\Bundle\CallBundle\Form\EventListener\ContactPhoneSubscriber')
                ->disableOriginalConstructor()
                ->getMock();

        $this->type = new CallType($contactPhoneSubscriber);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_call_form', $this->type->getName());
    }

    public function testBuildForm()
    {
        $expectedFields = array(
            'relatedAccount' => 'orocrm_account_select',
            'subject' => 'text',
            'relatedContact' => 'orocrm_contact_select',
            'contactPhoneNumber' => 'entity',
            'phoneNumber' => 'text',
            'notes' => 'textarea',
            'callDateTime' => 'oro_datetime',
            'callStatus' => 'entity',
            'duration' => 'oro_time_interval',
            'direction' => 'entity'
        );

        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $counter = 1;
        foreach ($expectedFields as $fieldName => $formType) {
            $builder->expects($this->at($counter))
                ->method('add')
                ->with($fieldName, $formType)
                ->will($this->returnSelf());
            $counter++;
        }
        $this->type->buildForm($builder, array());

    }
}
