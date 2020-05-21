<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Service\StateManager;

class CustomerExportWriterTest extends AbstractExportWriterTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getContainer()->get('oro_magento.importexport.writer.customer')->setTransport($this->transport);
    }

    public function testCreateNew()
    {
        $originId = time();

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        /** @var Customer $customer */
        $customer = $this->getReference('customer');
        $customer->setOriginId(null);

        $this->transport->expects($this->never())->method('getCustomerInfo');
        $this->transport->expects($this->never())->method('updateCustomer');
        $this->transport->expects($this->once())
            ->method('createCustomer')
            ->with($this->isType('array'))
            ->will($this->returnValue($originId));

        $jobResult = $this->getJobExecutor()->executeJob(
            'export',
            'magento_customer_export',
            [
                'channel'            => $customer->getChannel()->getId(),
                'entity'             => $customer,
                'changeSet'          => [],
                'twoWaySyncStrategy' => 'remote',
                'writer_skip_clear'  => true,
                'processorAlias'     => 'oro_magento'
            ]
        );

        $this->assertEmpty($jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        $customer = $this->getContainer()->get('doctrine')->getManager()->find(Customer::class, $customer->getId());

        $this->assertEquals($originId, $customer->getOriginId());

        $stateManager = new StateManager();
        $this->assertTrue($stateManager->isInState($customer->getSyncState(), 0));

        foreach ($customer->getAddresses() as $address) {
            $this->assertTrue($stateManager->isInState($address->getSyncState(), Address::SYNC_TO_MAGENTO));
        }
    }

    public function testUpdateExisting()
    {
        $newName = 'new name';

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        /** @var Customer $customer */
        $customer = $this->getReference('customer');
        $customer->setPassword(uniqid());

        $this->transport->expects($this->once())
            ->method('getCustomerInfo')
            ->will($this->returnValue(['firstname' => $newName, 'customer_id' => $customer->getOriginId()]));

        $this->transport->expects($this->once())
            ->method('updateCustomer')
            ->will($this->returnValue(true));

        $this->transport->expects($this->never())->method('createCustomer');

        $jobResult = $this->getJobExecutor()->executeJob(
            'export',
            'magento_customer_export',
            [
                'channel'            => $customer->getChannel()->getId(),
                'entity'             => $customer,
                'changeSet'          => [
                    'firstName' => [
                        'old' => $customer->getFirstName(),
                        'new' => $newName
                    ]
                ],
                'twoWaySyncStrategy' => 'remote',
                'writer_skip_clear'  => true,
                'processorAlias'     => 'oro_magento'
            ]
        );

        $this->assertEmpty($jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        $customer = $this->getContainer()->get('doctrine')->getManager()->find(Customer::class, $customer->getId());

        $this->assertEquals($newName, $customer->getFirstName());
        $this->assertEmpty($customer->getPassword());

        $stateManager = new StateManager();
        $this->assertTrue($stateManager->isInState($customer->getSyncState(), 0));
    }

    public function testRemovedStateIfFailed()
    {
        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        $e = new TransportException();
        $e->setFaultCode(102);

        $this->transport->expects($this->once())->method('getCustomerInfo')->will($this->throwException($e));
        $this->transport->expects($this->never())->method('updateCustomer');
        $this->transport->expects($this->never())->method('createCustomer');

        $jobResult = $this->getJobExecutor()->executeJob(
            'export',
            'magento_customer_export',
            [
                'channel'            => $customer->getChannel()->getId(),
                'entity'             => $customer,
                'changeSet'          => [],
                'twoWaySyncStrategy' => 'remote',
                'writer_skip_clear'  => true,
                'processorAlias'     => 'oro_magento'
            ]
        );

        $this->assertEmpty($jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        $customer = $this->getContainer()->get('doctrine')->getManager()->find(Customer::class, $customer->getId());

        $stateManager = new StateManager();
        $this->assertTrue($stateManager->isInState($customer->getSyncState(), Customer::MAGENTO_REMOVED));

        foreach ($customer->getAddresses() as $address) {
            $this->assertTrue($stateManager->isInState($address->getSyncState(), Address::MAGENTO_REMOVED));
        }
    }

    /**
     * @return JobExecutor
     */
    private function getJobExecutor()
    {
        return $this->getContainer()->get('oro_importexport.job_executor');
    }
}
