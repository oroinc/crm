<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Validator;

use OroCRM\Bundle\ChannelBundle\Validator\ChannelIntegrationConstraintValidator;
use OroCRM\Bundle\ChannelBundle\Validator\ChannelIntegrationConstraint;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelIntegrationConstraintValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var SettingsProvider */
    protected $provider;

    public function setUp()
    {
        $this->provider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testValidateException()
    {
        $constraint = $this->getMock('Symfony\Component\Validator\Constraint');
        $validator  = new ChannelIntegrationConstraintValidator($this->provider);
        $validator->validate(false, $constraint);
    }

    /**
     * @dataProvider validItemsDataProvider
     *
     * @param boolean $isValid
     * @param string $integrationType
     */
    public function testValidateValid($isValid, $integrationType)
    {
        $channel = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Entity\Channel')
            ->disableOriginalConstructor()->getMock();
        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()->getMock();

        $channel
            ->expects($this->once())
            ->method('getChannelType');

        $channel
            ->expects($this->once())
            ->method('getDataSource')
            ->will($this->returnValue($integrationType));

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

    public function validItemsDataProvider()
    {
        return [
            'valid'   => [
                'isValid'          => true,
                'integration'      => 'testType',
            ],
            'invalid' => [
                'isValid'          => false,
                'integration'      => null,
            ],
        ];
    }
}
