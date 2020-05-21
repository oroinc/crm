<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Service\StateManager;

class CustomerAddressExportWriterTest extends AbstractExportWriterTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getContainer()->get('oro_magento.importexport.processor.customer_address_export.job')->setTransport(
            $this->transport
        );
        $this->getContainer()->get('oro_magento.importexport.writer.customer_address')->setTransport(
            $this->transport
        );
    }

    public function testCreateNew()
    {
        $originId = time();

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_address_export', BatchStatus::FAILED));

        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        $this->transport->expects($this->never())->method('getCustomerAddressInfo');
        $this->transport->expects($this->never())->method('updateCustomerAddress');
        $this->transport->expects($this->once())
            ->method('createCustomerAddress')
            ->with($this->isType('string'), $this->isType('array'))
            ->will($this->returnValue($originId));

        /** @var Address $address */
        $address = $customer->getAddresses()->first();
        $address->setOriginId(null);

        $jobResult = $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_customer_address_export',
            [
                'channel'            => $address->getChannel()->getId(),
                'entity'             => $address,
                'changeSet'          => [],
                'twoWaySyncStrategy' => 'remote',
                'writer_skip_clear'  => true,
                'processorAlias'     => 'oro_magento'
            ]
        );

        $this->assertEmpty($jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_address_export', BatchStatus::FAILED));

        $address = $this->getContainer()->get('doctrine')->getManager()->find(Address::class, $address->getId());

        $this->assertEquals($originId, $address->getOriginId());

        $stateManager = new StateManager();
        $this->assertTrue($stateManager->isInState($address->getSyncState(), 0));
    }

    public function testUpdateExisting()
    {
        $newStreet = 'new street';

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_address_export', BatchStatus::FAILED));

        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        /** @var Address $address */
        $address = $customer->getAddresses()->first();

        $this->transport->expects($this->any())
            ->method('getCustomerAddressInfo')
            ->will(
                $this->returnValue(
                    [
                        'street'              => $newStreet,
                        'country_id'          => $address->getCountry()->getIso2Code(),
                        'customer_address_id' => $address->getOriginId(),
                        'customer_id'         => $customer->getOriginId()
                    ]
                )
            );

        $this->transport->expects($this->once())
            ->method('updateCustomerAddress')
            ->will($this->returnValue(true));

        $this->transport->expects($this->never())->method('createCustomerAddress');

        $jobResult = $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_customer_address_export',
            [
                'channel'            => $address->getChannel()->getId(),
                'entity'             => $address,
                'changeSet'          => [
                    'firstName' => [
                        'old' => $address->getCity(),
                        'new' => $newStreet
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
        $this->assertEmpty($this->getJobs('magento_customer_address_export', BatchStatus::FAILED));


        $address = $this->getContainer()->get('doctrine')->getManager()->find(Address::class, $address->getId());

        $this->assertEquals($newStreet, $address->getStreet());

        $stateManager = new StateManager();
        $this->assertTrue($stateManager->isInState($address->getSyncState(), 0));
    }

    public function testRemovedFailed()
    {
        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_address_export', BatchStatus::FAILED));

        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        /** @var Address $address */
        $address = $customer->getAddresses()->first();
        $addressId = $address->getId();

        $e = new TransportException();
        $e->setFaultCode(103);

        $this->transport->expects($this->once())
            ->method('getCustomerAddressInfo')
            ->will($this->throwException($e));
        $this->transport->expects($this->never())->method('updateCustomerAddress');
        $this->transport->expects($this->never())->method('createCustomerAddress');

        $jobResult = $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_customer_address_export',
            [
                'channel'            => $address->getChannel()->getId(),
                'entity'             => $address,
                'changeSet'          => [],
                'twoWaySyncStrategy' => 'remote',
                'writer_skip_clear'  => true,
                'processorAlias'     => 'oro_magento'
            ]
        );

        $this->assertEmpty($jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_address_export', BatchStatus::FAILED));

        $address = $this->getContainer()->get('doctrine')->getManager()->find(Address::class, $addressId);

        $this->assertFalse($customer->getAddresses()->contains($address));
    }
}
