<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AccountBundle\Form\Type\AccountSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var AccountSelectType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new AccountSelectType();
    }

    public function testName()
    {
        $this->assertEquals('oro_account_select', $this->type->getName());
    }

    public function testParent()
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::class, $this->type->getParent());
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->configureOptions($resolver);
    }
}
