<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Job\BatchStatus;

use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Service\StateManager;

/**
 * @dbIsolation
 */
class CustomerExportWriterTest extends AbstractExportWriterTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->getContainer()->get('orocrm_magento.importexport.writer.customer')->setTransport($this->transport);
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

        $jobResult = $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_customer_export',
            [
                'channel' => $customer->getChannel()->getId(),
                'entity' => $customer,
                'changeSet' => [],
                'twoWaySyncStrategy' => 'remote',
                'writer_skip_clear' => true,
                'processorAlias' => 'orocrm_magento'
            ]
        );

        $this->assertEmpty($jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        $this->getContainer()->get('doctrine')->getManager()->refresh($customer);

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

        $jobResult = $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_customer_export',
            [
                'channel' => $customer->getChannel()->getId(),
                'entity' => $customer,
                'changeSet' => [
                    'firstName' => [
                        'old' => $customer->getFirstName(),
                        'new' => $newName
                    ]
                ],
                'twoWaySyncStrategy' => 'remote',
                'writer_skip_clear' => true,
                'processorAlias' => 'orocrm_magento'
            ]
        );

        $this->assertEmpty($jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        $this->getContainer()->get('doctrine')->getManager()->refresh($customer);

        $this->assertEquals($newName, $customer->getFirstName());
        $this->assertEmpty($customer->getPassword());

        $stateManager = new StateManager();
        $this->assertTrue($stateManager->isInState($customer->getSyncState(), 0));

        foreach ($customer->getAddresses() as $address) {
            $this->assertTrue($stateManager->isInState($address->getSyncState(), Address::SYNC_TO_MAGENTO));
        }
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

        $jobResult = $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_customer_export',
            [
                'channel' => $customer->getChannel()->getId(),
                'entity' => $customer,
                'changeSet' => [],
                'twoWaySyncStrategy' => 'remote',
                'writer_skip_clear' => true,
                'processorAlias' => 'orocrm_magento'
            ]
        );

        $this->assertEmpty($jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        $stateManager = new StateManager();
        $this->assertTrue($stateManager->isInState($customer->getSyncState(), Customer::MAGENTO_REMOVED));

        foreach ($customer->getAddresses() as $address) {
            $this->assertTrue($stateManager->isInState($address->getSyncState(), Address::MAGENTO_REMOVED));
        }
    }

    public function testRemovedState()
    {
        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        $this->transport->expects($this->never())->method('getCustomerInfo');
        $this->transport->expects($this->never())->method('updateCustomer');
        $this->transport->expects($this->never())->method('createCustomer');

        $jobResult = $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_customer_export',
            [
                'channel' => $customer->getChannel()->getId(),
                'entity' => $customer,
                'changeSet' => [],
                'twoWaySyncStrategy' => 'remote',
                'writer_skip_clear' => true,
                'processorAlias' => 'orocrm_magento'
            ]
        );

        $this->assertEmpty($jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));
    }
}
