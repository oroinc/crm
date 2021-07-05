<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Validator;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Validator\ChannelCustomerIdentityConstraint;
use Oro\Bundle\ChannelBundle\Validator\ChannelCustomerIdentityConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ChannelCustomerIdentityConstraintValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new ChannelCustomerIdentityConstraintValidator();
    }

    public function testValidateException()
    {
        $this->expectException(UnexpectedTypeException::class);

        $constraint = $this->createMock(Constraint::class);
        $this->validator->validate(false, $constraint);
    }

    /**
     * @dataProvider validItemsDataProvider
     */
    public function testValidateValid(array $entities, string $customerIdentity, bool $isValid)
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

        if ($isValid) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation('oro.channel.form.customer_identity_selected_not_correctly.label')
                ->atPath('property.path.customerIdentity')
                ->assertRaised();
        }
    }

    public function validItemsDataProvider(): array
    {
        $entities = [
            'Oro\Bundle\AcmeBundle\Entity\Test1',
            'Oro\Bundle\AcmeBundle\Entity\Test2',
            'Oro\Bundle\AcmeBundle\Entity\Test3',
        ];

        return [
            'valid'   => [
                'entities'         => $entities,
                'customerIdentity' => 'Oro\Bundle\AcmeBundle\Entity\Test2',
                'isValid'          => true
            ],
            'invalid' => [
                'entities'         => $entities,
                'customerIdentity' => 'Oro\Bundle\AcmeBundle\Entity\Test0',
                'isValid'          => false
            ],
        ];
    }
}
