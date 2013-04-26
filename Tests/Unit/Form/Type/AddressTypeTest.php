<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

class AddressTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->type = new AddressType('Oro\Bundle\AddressBundle\Entity\Address', 'Oro\Bundle\AddressBundle\Entity\Value\AddressValue');
    }

    public function testAddEntityFields()
    {
        /** @vadr FormBuilderInterface $builder */
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->exactly(8))
            ->method('add')
            ->will($this->returnSelf());

        $builder->expects($this->at(0))
            ->method('add')
            ->with('id', 'hidden');
        $builder->expects($this->at(1))
            ->method('add')
            ->with('street', 'text');
        $builder->expects($this->at(2))
            ->method('add')
            ->with('street2', 'text');
        $builder->expects($this->at(3))
            ->method('add')
            ->with('city', 'text');
        $builder->expects($this->at(4))
            ->method('add')
            ->with('state', 'text');
        $builder->expects($this->at(5))
            ->method('add')
            ->with('postalCode', 'text');
        $builder->expects($this->at(6))
            ->method('add')
            ->with('country', 'oro_country');
        $builder->expects($this->at(7))
            ->method('add')
            ->with('mark', 'text');
        $this->type->addEntityFields($builder);
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

    public function testGetName()
    {
        $this->assertEquals('oro_address', $this->type->getName());
    }
}
