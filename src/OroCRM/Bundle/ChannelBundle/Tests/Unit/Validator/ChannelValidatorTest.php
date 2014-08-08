<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Validator;

use OroCRM\Bundle\ChannelBundle\Validator\ChannelValidator;

class ChannelValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testValidateException()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $constraint = $this->getMock('Symfony\Component\Validator\Constraint');
        $validator  = new ChannelValidator($translator);
        $validator->validate(false, $constraint);
    }

    /**
     * @dataProvider validItemsDataProvider
     */
    public function testValidateValid(array $entities, $customerIdentity, $isValid)
    {
        $channel = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Entity\Channel')
            ->disableOriginalConstructor()->getMock();
        $channel->expects($this->once())
            ->method('getEntities')
            ->will($this->returnValue($entities));
        $channel->expects($this->once())
            ->method('getCustomerIdentity')
            ->will($this->returnValue($customerIdentity));

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()->getMock();

        if ($isValid) {
            $context->expects($this->never())->method('addViolation');
        } else {
            $context->expects($this->once())->method('addViolation');
        }

        $constraint = $this->getMock('OroCRM\Bundle\ChannelBundle\Validator\Constraints\ChannelConstraint');
        $validator  = new ChannelValidator($translator);

        $validator->initialize($context);
        $validator->validate($channel, $constraint);
    }

    public function validItemsDataProvider()
    {
        $entities = [
            'OroCRM\Bundle\AcmeBundle\Entity\Test1',
            'OroCRM\Bundle\AcmeBundle\Entity\Test2',
            'OroCRM\Bundle\AcmeBundle\Entity\Test3',
        ];

        return [
            'valid'   => [
                'entities'         => $entities,
                'customerIdentity' => 'OroCRM\Bundle\AcmeBundle\Entity\Test2',
                'isValid'          => true
            ],
            'invalid' => [
                'entities'         => $entities,
                'customerIdentity' => 'OroCRM\Bundle\AcmeBundle\Entity\Test0',
                'isValid'          => false
            ],
        ];
    }
}
