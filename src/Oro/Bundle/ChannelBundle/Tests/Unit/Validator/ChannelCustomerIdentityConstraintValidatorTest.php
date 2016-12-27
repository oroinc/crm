<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Validator;

use Oro\Bundle\ChannelBundle\Validator\ChannelCustomerIdentityConstraintValidator;
use Oro\Bundle\ChannelBundle\Validator\ChannelCustomerIdentityConstraint;

class ChannelCustomerIdentityConstraintValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testValidateException()
    {
        $constraint = $this->createMock('Symfony\Component\Validator\Constraint');
        $validator  = new ChannelCustomerIdentityConstraintValidator();
        $validator->validate(false, $constraint);
    }

    /**
     * @dataProvider validItemsDataProvider
     *
     * @param array   $entities
     * @param string  $customerIdentity
     * @param boolean $isValid
     */
    public function testValidateValid(array $entities, $customerIdentity, $isValid)
    {
        $channel = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Entity\Channel')
            ->disableOriginalConstructor()->getMock();
        $channel->expects($this->once())
            ->method('getEntities')
            ->will($this->returnValue($entities));
        $channel->expects($this->once())
            ->method('getCustomerIdentity')
            ->will($this->returnValue($customerIdentity));

        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()->getMock();

        if ($isValid) {
            $context->expects($this->never())->method('addViolationAt');
        } else {
            $context->expects($this->once())->method('addViolationAt');
        }

        $constraint = new ChannelCustomerIdentityConstraint();
        $validator  = new ChannelCustomerIdentityConstraintValidator();

        $validator->initialize($context);
        $validator->validate($channel, $constraint);
    }

    public function validItemsDataProvider()
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
