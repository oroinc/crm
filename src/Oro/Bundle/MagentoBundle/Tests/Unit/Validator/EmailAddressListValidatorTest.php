<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Validator;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

use Oro\Bundle\MagentoBundle\Validator\Constraints\EmailAddressListConstraint;
use Oro\Bundle\MagentoBundle\Validator\EmailAddressListValidator;

class EmailAddressListValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailAddressListValidator
     */
    protected $validator;

    /**
     * @var EmailAddressListConstraint
     */
    protected $constraint;

    /**
     * @var ExecutionContextInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var ConstraintViolationBuilderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $violationBuilder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->validator = new EmailAddressListValidator();
        $this->constraint = new EmailAddressListConstraint();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->validator);
        unset($this->constraint);
        unset($this->context);
        unset($this->violationBuilder);
    }

    /**
     * @dataProvider testValidateProvider
     *
     * @param mixed  $emailAddressList
     * @param string $expectedViolationCode
     */
    public function testValidate($emailAddressList, $expectedViolationCode)
    {
        if ($expectedViolationCode) {
            $this->context
                ->expects($this->atLeastOnce())
                ->method('buildViolation')
                ->willReturn($this->violationBuilder);

            $this->violationBuilder
                ->expects($this->once())
                ->method('setCode')
                ->with($expectedViolationCode)
                ->willReturnSelf();

            $this->violationBuilder
                ->expects($this->atLeastOnce())
                ->method('setParameter')
                ->willReturnSelf();
        }
        $this->validator->initialize($this->context);
        $this->validator->validate($emailAddressList, $this->constraint);
    }

    /**
     * @return array
     */
    public function testValidateProvider()
    {
        return [
            'Empty email list' => [
                'emailAddressList' => null,
                'expectedViolationCode' => false
            ],
            'Valid email list' => [
                'emailAddressList' => ['test@email.com'],
                'expectedViolationCode' => false
            ],
            'Invalid email list' => [
                'emailAddressList' => ['test_something_email.com'],
                'expectedViolationCode' => EmailAddressListConstraint::INVALID_FORMAT_ERROR
            ],
        ];
    }
}
