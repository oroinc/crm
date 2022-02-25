<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Validator;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Validator\ChannelIntegrationConstraint;
use Oro\Bundle\ChannelBundle\Validator\ChannelIntegrationConstraintValidator;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ChannelIntegrationValidatorTest extends ConstraintValidatorTestCase
{
    /** @var SettingsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(SettingsProvider::class);
        parent::setUp();
    }

    protected function createValidator(): ChannelIntegrationConstraintValidator
    {
        return new ChannelIntegrationConstraintValidator($this->provider);
    }

    public function testGetTargets()
    {
        $constraint = new ChannelIntegrationConstraint();
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
    public function testValidate(bool $valid, ?Integration $integration)
    {
        $channel = $this->createMock(Channel::class);
        $channel->expects($this->once())
            ->method('getChannelType')
            ->willReturn('test_channel');
        $channel->expects($this->once())
            ->method('getDataSource')
            ->willReturn($integration);

        $this->provider->expects($this->once())
            ->method('getIntegrationType')
            ->willReturn('testType');

        $constraint = new ChannelIntegrationConstraint();
        $this->validator->validate($channel, $constraint);

        if ($valid) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation('oro.channel.form.integration_invalid.label')
                ->atPath('property.path.dataSource')
                ->assertRaised();
        }
    }

    public function validateDataProvider(): array
    {
        return [
            'valid'   => [
                'valid' => true,
                'integration' => new Integration(),
            ],
            'invalid' => [
                'valid' => false,
                'integration' => null,
            ],
        ];
    }
}
