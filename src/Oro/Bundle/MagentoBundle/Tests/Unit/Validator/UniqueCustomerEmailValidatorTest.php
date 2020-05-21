<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Validator;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\MagentoBundle\Validator\Constraints\UniqueCustomerEmailConstraint;
use Oro\Bundle\MagentoBundle\Validator\UniqueCustomerEmailValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueCustomerEmailValidatorTest extends \PHPUnit\Framework\TestCase
{
    const INTEGRATION_TYPE = '__INTEGRATION_TYPE__';

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $typesRegistry;

    /**
     * @var UniqueCustomerEmailValidator
     */
    protected $validator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $transport;

    /**
     * @var UniqueCustomerEmailConstraint
     */
    protected $constraint;

    protected function setUp(): void
    {
        $this->typesRegistry = $this
            ->getMockBuilder(TypesRegistry::class)
            ->setMethods(['getTransportTypeBySettingEntity'])
            ->getMock();

        $this->transport = $this->createMock(Transport::class);
        $this->validator = new UniqueCustomerEmailValidator($this->typesRegistry);
        $this->constraint = new UniqueCustomerEmailConstraint();
    }

    protected function tearDown(): void
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
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $context->expects($this->any())
            ->method('buildViolation')
            ->with($this->constraint->transportMessage)
            ->willReturn($builder);
        $builder->expects($this->any())
            ->method('atPath')
            ->with('email')
            ->willReturnSelf();
        $builder->expects($this->any())
            ->method('addViolation');

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
     *
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
            $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
            $context->expects($this->once())
                ->method('buildViolation')
                ->with($messageConstraint)
                ->willReturn($builder);
            $builder->expects($this->once())
                ->method('atPath')
                ->with('email')
                ->willReturnSelf();
            $builder->expects($this->once())
                ->method('addViolation');
        }

        $customer = $this->getCustomer();
        $transportProvider = $this->createMock(MagentoTransportInterface::class);
        $this->typesRegistry->method('getTransportTypeBySettingEntity')
            ->with($this->transport, self::INTEGRATION_TYPE)
            ->willReturn($transportProvider);

        $transportProvider
            ->expects($this->once())
            ->method('init');

        $transportProvider
            ->expects($this->once())
            ->method('isCustomerHasUniqueEmail')
            ->with($customer)
            ->will($this->returnValue($isUniqueEmail));

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
     * @return \PHPUnit\Framework\MockObject\MockObject
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
}
