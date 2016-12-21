<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AccountBundle\Form\Type\AccountSelectType;

class AccountSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccountSelectType
     */
    private $type;

    /**
     * Set up test environment
     */
    protected function setUp()
    {
        $this->type = new AccountSelectType();
    }

    public function testName()
    {
        $this->assertEquals('oro_account_select', $this->type->getName());
    }

    public function testParent()
    {
        $this->assertEquals('oro_entity_create_or_select_inline', $this->type->getParent());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }
}
