<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\AccountBundle\Form\Type\AccountSelectType;

class AccountSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccountSelectType
     */
    private $type;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->type = new AccountSelectType('orocrm_account_select');
    }

    public function testName()
    {
        $this->assertEquals('orocrm_account_select', $this->type->getName());
    }

    public function testParent()
    {
        $this->assertEquals('oro_jqueryselect2_hidden', $this->type->getParent());
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
}
