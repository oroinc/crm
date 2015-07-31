<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MagentoBundle\Form\Type\OrderAddressApiType;

class OrderAddressApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var OrderAddressApiType */
    protected $type;

    protected function setUp()
    {
        $this->type = new OrderAddressApiType();
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
            'label'        => 'text',
            'street'       => 'text',
            'street2'      => 'text',
            'city'         => 'text',
            'postalCode'   => 'text',
            'regionText'   => 'text',
            'namePrefix'   => 'text',
            'firstName'    => 'text',
            'middleName'   => 'text',
            'lastName'     => 'text',
            'nameSuffix'   => 'text',
            'phone'        => 'text',
            'primary'      => 'checkbox',
            'country'      => 'translatable_entity',
            'region'       => 'translatable_entity',
            'types'        => 'translatable_entity',
            'fax'          => 'text',
            'owner'        => 'orocrm_order_select',
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
                    'data_class'      => 'OroCRM\Bundle\MagentoBundle\Entity\OrderAddress',
                    'csrf_protection' => false,
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('order_address_api_type', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('form', $this->type->getParent());
    }
}
