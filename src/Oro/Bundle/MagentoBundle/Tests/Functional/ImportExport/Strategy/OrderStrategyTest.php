<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderAddress;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\OrderStrategy;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class OrderStrategyTest extends WebTestCase
{
    /**
     * @var OrderStrategy
     */
    private $strategy;

    /**
     * @var StepExecutionProxyContext
     */
    protected $context;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures([
            LoadMagentoChannel::class
        ]);

        $this->strategy = $this->getContainer()->get('oro_magento.import.strategy.order.add_or_update');
        $this->strategy->setEntityName(Order::class);

        $jobInstance = new JobInstance();
        $jobInstance->setRawConfiguration(['channel' => 3]);
        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);
        $this->stepExecution = new StepExecution('step', $jobExecution);
        $this->context = new StepExecutionProxyContext($this->stepExecution);
        $this->strategy->setImportExportContext($this->context);
        $this->strategy->setStepExecution($this->stepExecution);
    }

    public function testProcessAddressCountryTextSetTuNullWhenCountryIsSet()
    {
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(Order::class);
        $order = $em->getRepository(Order::class)->findOneBy([]);

        $street = 'Test_Street_001';
        $country = new Country('US');
        $address = new OrderAddress();
        $address->setCountry($country);
        $address->setCountryText('Test');
        $address->setStreet($street);

        $order->resetAddresses([$address]);

        /** @var Order $processedOrder */
        $processedOrder = $this->strategy->process($order);
        /** @var OrderAddress $processedAddress */
        $processedAddress = $processedOrder->getAddresses()->first();
        $this->assertInstanceOf(OrderAddress::class, $processedAddress);
        $this->assertSame($street, $processedAddress->getStreet());
        $this->assertInstanceOf(Country::class, $processedAddress->getCountry());
        $this->assertNull($processedAddress->getCountryText());
    }

    public function testProcessAddressCountryTextSetTuNullWhenCountryIsEmpty()
    {
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(Order::class);
        $order = $em->getRepository(Order::class)->findOneBy([]);

        $street = 'Test_Street_002';
        $address = new OrderAddress();
        $address->setCountryText('Test');
        $address->setStreet($street);

        $order->resetAddresses([$address]);

        /** @var Order $processedOrder */
        $processedOrder = $this->strategy->process($order);
        /** @var OrderAddress $processedAddress */
        $processedAddress = $processedOrder->getAddresses()->first();
        $this->assertInstanceOf(OrderAddress::class, $processedAddress);
        $this->assertSame($street, $processedAddress->getStreet());
        $this->assertNull($processedAddress->getCountry());
        $this->assertSame('Test', $processedAddress->getCountryText());
    }
}
