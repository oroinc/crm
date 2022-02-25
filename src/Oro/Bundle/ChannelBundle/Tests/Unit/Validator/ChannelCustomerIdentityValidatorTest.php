<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Validator;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Validator\ChannelCustomerIdentityConstraint;
use Oro\Bundle\ChannelBundle\Validator\ChannelCustomerIdentityConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ChannelCustomerIdentityValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ChannelCustomerIdentityConstraintValidator
    {
        return new ChannelCustomerIdentityConstraintValidator();
    }

    public function testGetTargets()
    {
        $constraint = new ChannelCustomerIdentityConstraint();
        self::assertEquals(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }

    public function testValidateException()
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(false, $this->createMock(Constraint::class));
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $entities, string $customerIdentity, bool $valid)
    {
        $channel = $this->createMock(Channel::class);
        $channel->expects($this->once())
            ->method('getEntities')
            ->willReturn($entities);
        $channel->expects($this->once())
            ->method('getCustomerIdentity')
            ->willReturn($customerIdentity);

        $constraint = new ChannelCustomerIdentityConstraint();
        $this->validator->validate($channel, $constraint);

        if ($valid) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation('oro.channel.form.customer_identity_selected_not_correctly.label')
                ->atPath('property.path.customerIdentity')
                ->assertRaised();
        }
    }

    public function validateDataProvider(): array
    {
        $entities = [
            'Oro\Bundle\AcmeBundle\Entity\Test1',
            'Oro\Bundle\AcmeBundle\Entity\Test2',
            'Oro\Bundle\AcmeBundle\Entity\Test3',
        ];

        return [
            'valid'   => [
                'entities' => $entities,
                'customerIdentity' => 'Oro\Bundle\AcmeBundle\Entity\Test2',
                'valid' => true
            ],
            'invalid' => [
                'entities' => $entities,
                'customerIdentity' => 'Oro\Bundle\AcmeBundle\Entity\Test0',
                'valid' => false
            ],
        ];
    }
}
