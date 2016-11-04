<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Validator\Constraints;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Oro\Bundle\SalesBundle\Tests\Unit\Stub\Customer1;
use Oro\Bundle\SalesBundle\Tests\Unit\Stub\Customer2;
use Oro\Bundle\SalesBundle\Tests\Unit\Stub\Opportunity;
use Oro\Bundle\SalesBundle\Validator\Constraints\Customer;
use Oro\Bundle\SalesBundle\Validator\Constraints\CustomerValidator;

class CustomerValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var CustomerValidator */
    protected $validator;

    public function setUp()
    {
        $customerManager = $this
            ->getMockBuilder('Oro\Bundle\SalesBundle\Manager\CustomerManager')
            ->disableOriginalConstructor()
            ->getMock();
        $customerManager->expects($this->any())
            ->method('hasMoreCustomers')
            ->will($this->returnCallback(function (Opportunity $opportunity) {
                return $opportunity->getCustomer1() && $opportunity->getCustomer2();
            }));
        $customerManager->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnCallback(function (Opportunity $opportunity) {
                return $opportunity->getCustomer1() ?: $opportunity->getCustomer2();
            }));

        $this->context = $this->getMock('Symfony\Component\Validator\Context\ExecutionContextInterface');

        $this->validator = new CustomerValidator($customerManager);
        $this->validator->initialize($this->context);
    }

    /**
     * @dataProvider validProvider
     */
    public function testValid(Customer $constraint, Opportunity $opportunity = null)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($opportunity, $constraint);
    }

    public function validProvider()
    {
        return [
            [
                new Customer(),
                null,
            ],
            [
                new Customer(),
                new Opportunity(),
            ],
            [
                new Customer(),
                (new Opportunity())
                    ->setCustomer1(new Customer1()),
            ],
            [
                new Customer(['required' => true]),
                (new Opportunity())
                    ->setCustomer1(new Customer1()),
            ],
            [
                new Customer(),
                (new Opportunity())
                    ->setCustomer2(new Customer2()),
            ],
            [
                new Customer(['required' => true]),
                (new Opportunity())
                    ->setCustomer2(new Customer2()),
            ],
        ];
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid(Customer $constraint, $message, Opportunity $opportunity = null)
    {
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($message);

        $this->validator->validate(
            $opportunity,
            $constraint
        );
    }

    public function invalidProvider()
    {
        $customer = new Customer();
        $requiredCustomer = new Customer(['required' => true]);

        return [
            [
                $requiredCustomer,
                $customer->requiredMessage,
                new Opportunity(),
            ],
            [
                $customer,
                $customer->message,
                (new Opportunity)
                    ->setCustomer1(new Customer1())
                    ->setCustomer2(new Customer2()),
            ],
        ];
    }
}
