<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Processor;

use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\ImportExportBundle\Serializer\SerializerInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use Oro\Bundle\IntegrationBundle\Tests\Unit\Fixture\Entity\TestTransport;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\ImportExport\Processor\CustomerAddressExportProcessor;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\MagentoBundle\Service\CustomerStateHandler;
use Oro\Bundle\MagentoBundle\Service\StateManager;

class CustomerAddressExportProcessorTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerAddressExportProcessor */
    protected $processor;

    /** @var MagentoTransportInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $transport;

    /** @var CustomerStateHandler */
    protected $stateHandler;

    /** @var SerializerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $serializer;

    protected function setUp(): void
    {
        $this->transport  = $this->createMock(MagentoTransportInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->processor  = new CustomerAddressExportProcessor();
        $this->processor->setTransport($this->transport);
        $this->processor->setStateHandler(new CustomerStateHandler(new StateManager()));
        $this->processor->setSerializer($this->serializer);
        $this->processor->setImportExportContext(new Context([]));
    }

    public function testStopProcessingRemovedObject()
    {
        $address  = $this->createAddress();
        $customer = $address->getOwner();

        $exception = new TransportException();
        $exception->setFaultCode(CustomerAddressExportProcessor::ADDRESS_NOT_EXISTS_CODE);

        $this->transport
            ->expects(self::once())
            ->method('getCustomerAddressInfo')
            ->willThrowException($exception);

        $result = $this->processor->process($address);

        $this->assertNull($result);
        $this->assertEmpty($customer->getAddresses()->toArray());
    }

    public function testExpectedInvalidArgumentException()
    {
        $this->expectException(\Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Expected instance of Oro\Bundle\MagentoBundle\Entity\Address, "stdClass" given.'
        );

        $object = new \stdClass();
        $this->processor->process($object);
    }

    /**
     * @return Address
     */
    protected function createAddress()
    {
        $customer = new Customer();
        $object   = new Address();
        $object->setOwner($customer);
        $object->setOriginId(12345);
        $object->setChannel((new Channel())->setTransport(new TestTransport()));

        return $object;
    }
}
