<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\CustomerStrategy;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadWebsitesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerStrategyTest extends WebTestCase
{
    /**
     * @var CustomerStrategy
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
            LoadWebsitesData::class
        ]);

        $this->strategy = $this->getContainer()->get('oro_magento.import.strategy.customer.add_or_update');
        $this->strategy->setEntityName(Customer::class);

        $jobInstance = new JobInstance();
        $jobInstance->setRawConfiguration(['channel' => $this->getReference('integration')]);
        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);
        $this->stepExecution = new StepExecution('step', $jobExecution);
        $this->context = new StepExecutionProxyContext($this->stepExecution);
        $this->strategy->setImportExportContext($this->context);
        $this->strategy->setStepExecution($this->stepExecution);
    }

    public function testProcessSameCustomerWithChangedWebsite()
    {
        /** @var Customer $originalCustomer */
        $originalCustomer = $this->getReference('customer');

        $customer = new Customer();
        $customer->setOriginId($originalCustomer->getOriginId());
        $customer->setChannel($originalCustomer->getChannel());

        /** @var Website $newWebsite */
        $newWebsite = $this->getReference('magento.website2');
        $customer->setWebsite($newWebsite);

        /** @var Customer $processedCustomer */
        $processedCustomer = $this->strategy->process($customer);
        $this->assertInstanceOf(Customer::class, $processedCustomer);
        $this->assertNotEmpty($processedCustomer->getId());
        $this->assertSame($newWebsite, $processedCustomer->getWebsite());
    }
}
