<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Validator;

use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\ExecutionContextInterface;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\MagentoBundle\Validator\UniqueCustomerEmailValidator;
use Oro\Bundle\MagentoBundle\Validator\Constraints\UniqueCustomerEmailConstraint;


class UniqueCustomerEmailValidatorTest extends \PHPUnit_Framework_TestCase
{
    const INTEGRATION_TYPE = '__INTEGRATION_TYPE__';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typesRegistry;

    /**
     * @var UniqueCustomerEmailValidator
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transport;

    /**
     * @var UniqueCustomerEmailConstraint
     */
    protected $constraint;

    protected function setUp()
    {
        $this->typesRegistry = $this
            ->getMockBuilder(TypesRegistry::class)
            ->setMethods(['getTransportTypeBySettingEntity'])
            ->getMock();

        $this->transport = $this->createMock(Transport::class);
        $this->validator = new UniqueCustomerEmailValidator($this->typesRegistry);
        $this->constraint = new UniqueCustomerEmailConstraint();
    }

    protected function tearDown()
    {
        unset($this->validator, $this->typesRegistry, $this->constraint, $this->transport);
    }

    public function testValidateIncorrectInstance()
    {
        $value = new \stdClass();

        $this->typesRegistry->expects($this->never())
            ->method($this->anything());

        $this->validator->validate($value, $this->constraint);
    }

    public function testIncorrectTransportProvider()
    {
        $this->expectException(UnexpectedTypeException::class);
        $incorrectTransportProvider = $this->getMockBuilder(TransportInterface::class)->getMock();
        $customer = $this->getCustomer();
        $this->typesRegistry->method('getTransportTypeBySettingEntity')
            ->with($this->transport, self::INTEGRATION_TYPE)
            ->willReturn($incorrectTransportProvider);

        $this->validator->validate($customer, $this->constraint);
    }

    public function testShouldAddViolationWhenTransportInitFails()
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->any())
            ->method('addViolationAt')
            ->with('email', $this->constraint->transportMessage);

        $transportProvider = $this->createMock(MagentoTransportInterface::class);
        $transportProvider
            ->expects($this->once())
            ->method('init')
            ->willThrowException(new \RuntimeException());
        $this->typesRegistry->method('getTransportTypeBySettingEntity')
            ->with($this->transport, self::INTEGRATION_TYPE)
            ->willReturn($transportProvider);

        $customer = $this->getCustomer();

        $this->validator->initialize($context);
        $this->validator->validate($customer, $this->constraint);
    }

    /**
     * @dataProvider uniqueProvider
     * @param bool $isUniqueEmail
     * @param string $messageConstraint
     */
    public function testValidateCorrect($isUniqueEmail, $messageConstraint)
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        if (null === $messageConstraint) {
            $context->expects($this->never())
                ->method($this->anything());
        } else {
            $context
                ->expects($this->once())
                ->method('addViolationAt')
                ->with('email', $messageConstraint);
        }

        $transportProvider = $this->createMock(MagentoTransportInterface::class);
        $this->typesRegistry->method('getTransportTypeBySettingEntity')
            ->with($this->transport, self::INTEGRATION_TYPE)
            ->willReturn($transportProvider);

        $transportProvider
            ->expects($this->once())
            ->method('init')
            ->willThrowException(new \RuntimeException());

        $customer = $this->getCustomer();
        $this->assertCustomerUniqueEmailCalls($isUniqueEmail, $customer);

        $this->validator->initialize($context);
        $this->validator->validate($customer, $this->constraint);
    }

    /**
     * @return array
     */
    public function uniqueProvider()
    {
        return [
            'Email unique' => [
                true,
                null
            ],
            'Non-unique email' => [
                false,
                'oro.magento.unique_customer_email.message'
            ]
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCustomer()
    {
        $channel = $this->getMockBuilder(Channel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTransport', 'getType'])
            ->getMock();

        $channel->method('getTransport')->willReturn($this->transport);
        $channel->method('getType')->willReturn(self::INTEGRATION_TYPE);

        $customer = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        $customer->expects($this->any())
            ->method('getChannel')
            ->willReturn($channel);

        return $customer;
    }

    /**
     * @param bool $isUniqueEmail
     * @param \PHPUnit_Framework_MockObject_MockObject $customer
     */
    protected function assertCustomerUniqueEmailCalls($isUniqueEmail, $customer)
    {
        $this->transport->expects($this->once())
            ->method('init');

        $this->transport->expects($this->once())
            ->method('isCustomerHasUniqueEmail')
            ->with($customer)
            ->will($this->returnValue($isUniqueEmail));
    }
}
