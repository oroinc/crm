<?php


namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MagentoBundle\Form\Type\CustomerApiType;

class CustomerApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerApiType */
    protected $type;

    protected function setUp()
    {
        $this->type = new CustomerApiType();
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

        $builder->expects($this->exactly(2))
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface'));

        $expectedFields = [
            'namePrefix'   => 'text',
            'firstName'    => 'text',
            'middleName'   => 'text',
            'lastName'     => 'text',
            'nameSuffix'   => 'text',
            'gender'       => 'oro_gender',
            'birthday'     => 'oro_date',
            'email'        => 'text',
            'originId'     => 'text',
            'website'      => 'translatable_entity',
            'store'        => 'translatable_entity',
            'group'        => 'translatable_entity',
            'dataChannel'  => 'translatable_entity',
            'addresses'    => 'oro_address_collection',
            'owner'        => 'translatable_entity'
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
                    'data_class'      => 'OroCRM\Bundle\MagentoBundle\Entity\Customer',
                    'csrf_protection' => false
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('api_customer_type', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('form', $this->type->getParent());
    }
}
