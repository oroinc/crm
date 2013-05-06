<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\ContactBundle\Form\Type\ContactType;

class ContactTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactType
     */
    protected $type;

    protected function setUp()
    {
        $flexibleManager = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new ContactType($flexibleManager,'oro_contact');
    }

    public function testAddEntityFields()
    {
        /** @var FormBuilderInterface $builder */
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->at(1))
            ->method('add')
            ->with('name');
        $this->type->addEntityFields($builder);
    }

    public function testAddDynamicAttributesFields()
    {
        /** @var FormBuilderInterface $builder */
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->once())
            ->method('add')
            ->with('attributes', 'collection');
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
        $this->assertEquals('oro_contact', $this->type->getName());
    }
}
