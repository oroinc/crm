<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\AccountBundle\Form\Type\AccountType;

class AccountTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccountType
     */
    protected $type;

    protected function setUp()
    {
        $this->type = new AccountType('Oro\Bundle\AccountBundle\Entity\Account', 'Oro\Bundle\AccountBundle\Entity\AccountValue');
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
        $this->assertEquals('oro_account', $this->type->getName());
    }
}