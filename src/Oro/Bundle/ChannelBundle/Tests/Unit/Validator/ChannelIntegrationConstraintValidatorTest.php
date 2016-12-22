<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Validator;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Validator\ChannelIntegrationConstraint;
use Oro\Bundle\ChannelBundle\Validator\ChannelIntegrationConstraintValidator;

class ChannelIntegrationConstraintValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $provider;

    public function setUp()
    {
        $this->provider = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();
    }

    public function tearDown()
    {
        unset($this->provider);
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testValidateException()
    {
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
        $channel = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Entity\Channel')
            ->disableOriginalConstructor()->getMock();
        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()->getMock();

        $channel
            ->expects($this->once())
            ->method('getChannelType');

        $channel
            ->expects($this->once())
            ->method('getDataSource')
            ->will($this->returnValue($integration));

        $this->provider
            ->expects($this->once())
            ->method('getIntegrationType')
            ->will($this->returnValue('testType'));

        if ($isValid) {
            $context->expects($this->never())->method('addViolationAt');
        } else {
            $context->expects($this->once())->method('addViolationAt');
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
