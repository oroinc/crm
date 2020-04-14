<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Validator;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Validator\ChannelIntegrationConstraint;
use Oro\Bundle\ChannelBundle\Validator\ChannelIntegrationConstraintValidator;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ChannelIntegrationConstraintValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var SettingsProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();
    }

    protected function tearDown(): void
    {
        unset($this->provider);
    }

    public function testValidateException()
    {
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedTypeException::class);
        $constraint = $this->createMock('Symfony\Component\Validator\Constraint');
        $validator  = new ChannelIntegrationConstraintValidator($this->provider);
        $validator->validate(false, $constraint);
    }

    /**
     * @dataProvider validItemsDataProvider
     *
     * @param boolean          $isValid
     * @param null|Integration $integration
     */
    public function testValidateValid($isValid, $integration)
    {
        $channel = $this->createMock(Channel::class);
        $context = $this->createMock(ExecutionContext::class);

        $channelType = 'test_channel';

        $channel
            ->expects($this->once())
            ->method('getChannelType')
            ->willReturn($channelType);

        $channel
            ->expects($this->once())
            ->method('getDataSource')
            ->will($this->returnValue($integration));

        $this->provider
            ->expects($this->once())
            ->method('getIntegrationType')
            ->will($this->returnValue('testType'));

        if ($isValid) {
            $context->expects($this->never())->method('buildViolation');
        } else {
            $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
            $context->expects($this->once())
                ->method('buildViolation')
                ->willReturn($builder);
            $builder->expects($this->once())
                ->method('atPath')
                ->willReturnSelf();
            $builder->expects($this->once())
                ->method('addViolation');
        }

        $constraint = new ChannelIntegrationConstraint();
        $validator  = new ChannelIntegrationConstraintValidator($this->provider);

        $validator->initialize($context);
        $validator->validate($channel, $constraint);
    }

    /**
     * @return array
     */
    public function validItemsDataProvider()
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
