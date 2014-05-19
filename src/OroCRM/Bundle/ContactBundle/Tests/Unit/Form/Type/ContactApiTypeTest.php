<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use OroCRM\Bundle\ContactBundle\Form\Type\ContactApiType;

class ContactApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactApiType
     */
    protected $type;

    /**
     * Setup test env
     */
    protected function setUp()
    {
        $this->type = new ContactApiType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface'));

        $this->type->buildForm($builder, array());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                array(
                    'csrf_protection' => false,
                )
            );
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('contact', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('orocrm_contact', $this->type->getParent());
    }
}
