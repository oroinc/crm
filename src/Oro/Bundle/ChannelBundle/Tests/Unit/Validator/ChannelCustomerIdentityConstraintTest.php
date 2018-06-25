<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Validator;

use Oro\Bundle\ChannelBundle\Validator\ChannelCustomerIdentityConstraint;

class ChannelCustomerIdentityConstraintTest extends \PHPUnit\Framework\TestCase
{
    public function testGetTargets()
    {
        $constraint = new ChannelCustomerIdentityConstraint();
        $this->assertSame('class', $constraint->getTargets());
    }
}
