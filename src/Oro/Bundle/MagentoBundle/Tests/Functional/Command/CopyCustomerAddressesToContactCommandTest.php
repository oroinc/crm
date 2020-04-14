<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Command;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\CopyCustomerAddressToContact\LoadMagentoChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class CopyCustomerAddressesToContactCommandTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadMagentoChannel::class]);
    }

    public function testCommand()
    {
        $entityManager = $this->getContainer()->get('doctrine');
        $repo = $entityManager->getRepository('Oro\Bundle\MagentoBundle\Entity\Customer');
        $customers = $repo->findAll();
        /** @var Customer $customer */
        foreach ($customers as $customer) {
            self::assertEquals(0, $customer->getContact()->getAddresses()->count());
        }

        $result = $this->runCommand('oro:magento:copy-data-to-contact:addresses', ['--batch-size=2']);

        $customers = $repo->findAll();
        /** @var Customer $customer */
        foreach ($customers as $customer) {
            self::assertEquals(1, $customer->getContact()->getAddresses()->count());
        }

        static::assertStringContainsString('Executing command started.', $result);
        static::assertStringContainsString('Executing command finished.', $result);
    }

    public function testConvertAddressForCustomerById()
    {
        $testCustomer = $this->getReference('customer_1');
        $id = $testCustomer->getId();

        $entityManager = $this->getContainer()->get('doctrine');
        $repo = $entityManager->getRepository('Oro\Bundle\MagentoBundle\Entity\Customer');
        /** @var Customer $customer */
        $customer = $repo->find($id);
        self::assertEquals(0, $customer->getContact()->getAddresses()->count());

        $result = $this->runCommand('oro:magento:copy-data-to-contact:addresses', ['--id=' . $id]);

        /** @var Customer $customer */
        $customer = $repo->find($id);
        self::assertEquals(1, $customer->getContact()->getAddresses()->count());

        static::assertStringContainsString('Executing command started.', $result);
        static::assertStringContainsString('Executing command finished.', $result);
    }

    public function testConvertAddressForCustomerByIds()
    {
        $testCustomerId1 = $this->getReference('customer_1')->getId();
        $testCustomerId2 = $this->getReference('customer_2')->getId();

        $entityManager = $this->getContainer()->get('doctrine');
        $repo = $entityManager->getRepository('Oro\Bundle\MagentoBundle\Entity\Customer');
        $customers = $repo->findBy(['id' => [$testCustomerId1, $testCustomerId2]]);
        /** @var Customer $customer */
        foreach ($customers as $customer) {
            self::assertEquals(0, $customer->getContact()->getAddresses()->count());
        }

        $result = $this->runCommand('oro:magento:copy-data-to-contact:addresses', [
            '--id=' . $testCustomerId1,
            '--id=' . $testCustomerId2
        ]);

        $customers = $repo->findBy(['id' => [$testCustomerId1, $testCustomerId2]]);
        /** @var Customer $customer */
        foreach ($customers as $customer) {
            self::assertEquals(1, $customer->getContact()->getAddresses()->count());
        }

        static::assertStringContainsString('Executing command started.', $result);
        static::assertStringContainsString('Executing command finished.', $result);
    }

    public function testConvertAddressForCustomerByIntegrationId()
    {
        $integrationId = $this->getReference('integration')->getId();
        $entityManager = $this->getContainer()->get('doctrine');
        $repo = $entityManager->getRepository('Oro\Bundle\MagentoBundle\Entity\Customer');
        $customers = $repo->findBy(['channel' => $integrationId]);
        /** @var Customer $customer */
        foreach ($customers as $customer) {
            self::assertEquals(0, $customer->getContact()->getAddresses()->count());
        }

        $result = $this->runCommand('oro:magento:copy-data-to-contact:addresses', [
            '--integration-id=' . $integrationId,
            '--batch-size=2'
        ]);

        $customers = $repo->findBy(['channel' => $integrationId]);
        /** @var Customer $customer */
        foreach ($customers as $customer) {
            self::assertEquals(1, $customer->getContact()->getAddresses()->count());
        }

        static::assertStringContainsString('Executing command started.', $result);
        static::assertStringContainsString('Executing command finished.', $result);
    }

    public function testConvertAddressForCustomerByIdAndAccountHasAddress()
    {
        $testCustomerId1 = $this->getReference('customer_1')->getId();

        $entityManager = $this->getContainer()->get('doctrine');
        $repo = $entityManager->getRepository('Oro\Bundle\MagentoBundle\Entity\Customer');
        /** @var Customer $customer */
        $customer = $repo->find($testCustomerId1);
        self::assertEquals(0, $customer->getContact()->getAddresses()->count());

        for ($i = 0; $i < 2; $i++) {
            $result = $this->runCommand('oro:magento:copy-data-to-contact:addresses', [
                '--id=' . $testCustomerId1
            ]);
        }

        /** @var Customer $customer */
        $customer = $repo->find($testCustomerId1);
        self::assertEquals(1, $customer->getContact()->getAddresses()->count());

        static::assertStringContainsString('Executing command started.', $result);
        static::assertStringContainsString('Executing command finished.', $result);
    }
}
