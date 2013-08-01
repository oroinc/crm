<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use OroCRM\Bundle\ContactBundle\Form\Type\ContactApiType;
use Oro\Bundle\AddressBundle\Form\EventListener\AddressCollectionTypeSubscriber;
use Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber;

class ContactApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactApiType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->type = new ContactApiType(
            'OroCRM\Bundle\ContactBundle\Entity\Contact',
            'OroCRM\Bundle\ContactBundle\Entity\ContactAddress'
        );
    }

    public function testSetDefaultOptions()
    {
        /** @var OptionsResolverInterface $resolver */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testBuildForm()
    {
        $subscribers = array();

        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->any())
            ->method('add')
            ->will($this->returnSelf());
        $builder->expects($this->any())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface'))
            ->will(
                $this->returnCallback(
                    function (EventSubscriberInterface $subscriber) use (&$subscribers) {
                        $subscribers[] = $subscriber;
                    }
                )
            );
        $this->type->buildForm($builder, array());

        $this->assertCount(2, $subscribers);
        $this->assertInstanceOf(
            'Oro\Bundle\AddressBundle\Form\EventListener\AddressCollectionTypeSubscriber',
            $subscribers[0]
        );
        $this->assertInstanceOf(
            'Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber',
            $subscribers[1]
        );
    }

    public function testGetName()
    {
        $this->assertEquals('contact', $this->type->getName());
    }
}
