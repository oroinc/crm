<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MagentoBundle\Form\Type\CartAddressApiType;

class CartAddressApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var CartAddressApiType */
    protected $type;

    protected function setUp()
    {
        $this->type = new CartAddressApiType();
    }

    protected function tearDown()
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
            'phone' => 'text'
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

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class'           => 'OroCRM\Bundle\MagentoBundle\Entity\CartAddress',
                    'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                    'single_form'          => true,
                    'csrf_protection'      => false
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('cart_address_api_type', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_address', $this->type->getParent());
    }
}
