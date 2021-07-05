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

class ChannelIntegrationConstraintValidatorTest extends ConstraintValidatorTestCase
{
    /** @var SettingsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(SettingsProvider::class);
        parent::setUp();
    }

    protected function createValidator()
    {
        return new ChannelIntegrationConstraintValidator($this->provider);
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
    public function testValidateValid(bool $isValid, ?Integration $integration)
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

        if ($isValid) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation('oro.channel.form.integration_invalid.label')
                ->atPath('property.path.dataSource')
                ->assertRaised();
        }
    }

    public function validItemsDataProvider(): array
    {
        return [
            'valid'   => [
                '$isValid'     => true,
                '$integration' => new Integration(),
            ],
            'invalid' => [
                '$isValid'     => false,
                '$integration' => null,
            ],
        ];
    }
}
