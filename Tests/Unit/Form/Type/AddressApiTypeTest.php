<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressApiType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

class AddressApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressApiType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->type = new AddressApiType('Oro\Bundle\AddressBundle\Entity\Address', 'Oro\Bundle\AddressBundle\Entity\Value\AddressValue');
    }

    public function testAddEntityFields()
    {
        /** @var FormBuilderInterface $builder */
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->any())
            ->method('add')
            ->will($this->returnSelf());
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber'));
        $this->type->addEntityFields($builder);
    }

    public function testAddDynamicAttributesFields()
    {
        /** @var FormBuilderInterface $builder */
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->any())
            ->method('add')
            ->with('attributes', 'collection', $this->isType('array'));
        $this->type->addDynamicAttributesFields($builder);
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
        $this->assertEquals('address', $this->type->getName());
    }
}
