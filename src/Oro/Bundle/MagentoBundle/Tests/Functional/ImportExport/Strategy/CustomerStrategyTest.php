<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\CustomerStrategy;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadWebsitesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
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
            LoadMagentoChannel::class
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

    public function testProcessWhenNewCustomerWithDuplicateEmailIsProcessed()
    {
        /** @var Customer $originalCustomer */
        $originalCustomer = $this->getReference('customer');

        $duplicateEmailCustomer = new Customer();
        $uniqueOriginId = 123456789;
        $duplicateEmailCustomer->setOriginId($uniqueOriginId);
        $duplicateEmailCustomer->setChannel($originalCustomer->getChannel());
        $duplicateEmailCustomer->setEmail($originalCustomer->getEmail());
        $duplicateEmailCustomer->setWebsite($originalCustomer->getWebsite());

        /** @var Customer $duplicateEmailCustomer */
        $resultCustomer = $this->strategy->process($duplicateEmailCustomer);
        $this->assertSame($duplicateEmailCustomer, $resultCustomer);
    }
}
