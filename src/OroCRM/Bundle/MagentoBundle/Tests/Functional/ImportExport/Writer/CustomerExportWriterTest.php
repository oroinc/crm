<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Job\BatchStatus;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Service\StateManager;

/**
 * @outputBuffering enabled
 */
class CustomerExportWriterTest extends AbstractExportWriterTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->getContainer()->get('orocrm_magento.importexport.writer.customer')->setTransport($this->transport);
    }

    public function testUpdateExisting()
    {
        $newName = 'new name';

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        /** @var Channel $channel */
        $channel = $this->getReference('default_channel');

        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        $this->transport->expects($this->once())
            ->method('getCustomerInfo')
            ->will($this->returnValue([]));

        $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_customer_export',
            [
                'channel' => $channel->getId(),
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

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        $this->getContainer()->get('doctrine')->getManager()->refresh($customer);

        $this->assertEquals($newName, $customer->getFirstName());
    }

    public function testCreateNew()
    {
        $newName = 'new name';

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        /** @var Channel $channel */
        $channel = $this->getReference('default_channel');

        /** @var Customer $customer */
        $customer = $this->getReference('customer');
        $customer->setOriginId(null);

        $this->transport->expects($this->once())
            ->method('getCustomerInfo')
            ->will($this->returnValue([]));

        $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_customer_export',
            [
                'channel' => $channel->getId(),
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

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        $this->getContainer()->get('doctrine')->getManager()->refresh($customer);

        $this->assertEquals($newName, $customer->getFirstName());
    }

    public function testRemovedState()
    {
        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));

        /** @var Channel $channel */
        $channel = $this->getReference('default_channel');

        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        $stateManager = new StateManager();
        $stateManager->addState($customer, 'syncState', Customer::MAGENTO_REMOVED);

        $this->transport->expects($this->never())->method('getCustomerInfo');
        $this->transport->expects($this->never())->method('updateCustomer');
        $this->transport->expects($this->never())->method('createCustomer');

        $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_customer_export',
            [
                'channel' => $channel->getId(),
                'entity' => $customer,
                'changeSet' => [],
                'twoWaySyncStrategy' => 'remote',
                'writer_skip_clear' => true,
                'processorAlias' => 'orocrm_magento'
            ]
        );

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_customer_export', BatchStatus::FAILED));
    }
}
