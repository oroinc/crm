<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\MagentoBundle\Form\Type\CartAddressApiType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CartAddressApiTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CartAddressApiType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new CartAddressApiType();
    }

    protected function tearDown(): void
    {
        unset($this->type);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface'));

        $expectedFields = [
            'phone' => TextType::class,
            'countryText' => TextType::class
        ];

        $builder->expects($this->exactly(count($expectedFields)))
            ->method('add');

        $counter = 0;
        foreach ($expectedFields as $fieldName => $formType) {
            $builder->expects($this->at($counter))
                ->method('add')
                ->with($fieldName, $formType)
                ->will($this->returnSelf());
            $counter++;
        }

        $this->type->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class'           => 'Oro\Bundle\MagentoBundle\Entity\CartAddress',
                    'single_form'          => true,
                    'csrf_protection'      => false
                ]
            );

        $this->type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(AddressType::class, $this->type->getParent());
    }
}
