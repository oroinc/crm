<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Validator;

use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use Oro\Bundle\MagentoBundle\Validator\Constraints\UniqueCustomerEmailConstraint;
use Oro\Bundle\MagentoBundle\Validator\UniqueCustomerEmailValidator;

class UniqueCustomerEmailValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transport;

    /**
     * @var UniqueCustomerEmailValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->transport = $this
            ->getMockBuilder('Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface')
            ->getMock();

        $this->validator = new UniqueCustomerEmailValidator($this->transport);
    }

    protected function tearDown()
    {
        unset($this->validator, $this->transport);
    }

    public function testValidateIncorrectInstance()
    {
        $value = new \stdClass();
        $constraint = new UniqueCustomerEmailConstraint();

        $this->transport->expects($this->never())
            ->method($this->anything());

        $this->validator->validate($value, $constraint);
    }

    /**
     * @dataProvider correctCustomersDataProvider
     * @param array $customers
     */
    public function testValidateCorrect(array $customers)
    {
        $constraint = new UniqueCustomerEmailConstraint();

        $context = $this->createMock('Symfony\Component\Validator\ExecutionContextInterface');
        $context->expects($this->never())
            ->method($this->anything());

        $this->assertTransportCalls($customers);

        $customer = $this->getCustomer();

        $this->validator->initialize($context);
        $this->validator->validate($customer, $constraint);
    }

    /**
     * @return array
     */
    public function correctCustomersDataProvider()
    {
        return [
            [[]],
            [[['customer_id' => 1]]]
        ];
    }

    /**
     * @dataProvider incorrectCustomersDataProvider
     * @param array $customers
     */
    public function testValidateIncorrect(array $customers)
    {
        $constraint = new UniqueCustomerEmailConstraint();

        $context = $this->createMock('Symfony\Component\Validator\ExecutionContextInterface');
        $context->expects($this->once())
            ->method('addViolationAt')
            ->with('email', $constraint->message);

        $this->assertTransportCalls($customers);

        $customer = $this->getCustomer();

        $this->validator->initialize($context);
        $this->validator->validate($customer, $constraint);
    }

    /**
     * @return array
     */
    public function incorrectCustomersDataProvider()
    {
        return [
            [[['customer_id' => 2]]],
            [[['customer_id' => '']]],
            [[['increment_id' => 5]]],
            [[['customer_id' => 1], ['customer_id' => 2]]]
        ];
    }

    public function testShouldAddViolationWhenTransportInitFails()
    {
        $constraint = new UniqueCustomerEmailConstraint();

        $context = $this->createMock('Symfony\Component\Validator\ExecutionContextInterface');
        $context->expects($this->any())
            ->method('addViolationAt')
            ->with('email', $constraint->transportMessage);

        $this->transport->expects($this->any())
            ->method('init')
            ->will($this->throwException(new \RuntimeException()));

        $customer = $this->getCustomer();

        $this->validator->initialize($context);
        $this->validator->validate($customer, $constraint);
    }

    public function testShouldAddViolationWhenTransportCallFails()
    {
        $constraint = new UniqueCustomerEmailConstraint();

        $context = $this->createMock('Symfony\Component\Validator\ExecutionContextInterface');
        $context->expects($this->any())
            ->method('addViolationAt')
            ->with('email', $constraint->transportMessage);

        $this->transport->expects($this->any())
            ->method('init');

        $this->transport->expects($this->any())
            ->method('call')
            ->will($this->throwException(new \RuntimeException()));

        $customer = $this->getCustomer();

        $this->validator->initialize($context);
        $this->validator->validate($customer, $constraint);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCustomer()
    {
        $transport = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Transport')
            ->disableOriginalConstructor()
            ->getMock();

        $channel = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $channel->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue($transport));

        $store = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())
            ->method('getOriginId')
            ->will($this->returnValue(42));

        $customer = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($channel));

        $customer->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $customer->expects($this->any())
            ->method('getOriginId')
            ->will($this->returnValue(1));

        return $customer;
    }

    /**
     * @param array $customers
     */
    protected function assertTransportCalls(array $customers)
    {
        $this->transport->expects($this->once())
            ->method('init');

        $this->transport->expects($this->once())
            ->method('call')
            ->with(SoapTransport::ACTION_CUSTOMER_LIST, $this->isType('array'))
            ->will($this->returnValue($customers));
    }
}
