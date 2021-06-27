<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ContactBundle\Form\Type\ContactApiType;
use Oro\Bundle\ContactBundle\Form\Type\ContactType;

class ContactApiTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContactApiType
     */
    protected $type;

    /**
     * Setup test env
     */
    protected function setUp(): void
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

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                array(
                    'csrf_protection' => false,
                )
            );
        $this->type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(ContactType::class, $this->type->getParent());
    }
}
