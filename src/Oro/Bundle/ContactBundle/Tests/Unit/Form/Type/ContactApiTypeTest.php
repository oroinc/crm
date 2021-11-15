<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ContactBundle\Form\Type\ContactApiType;
use Oro\Bundle\ContactBundle\Form\Type\ContactType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactApiTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContactApiType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new ContactApiType();
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf(EventSubscriberInterface::class));

        $this->type->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(['csrf_protection' => false]);
        $this->type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(ContactType::class, $this->type->getParent());
    }
}
