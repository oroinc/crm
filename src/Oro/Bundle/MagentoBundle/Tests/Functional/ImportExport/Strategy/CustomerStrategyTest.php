<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\CustomerStrategy;
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

    protected function setUp(): void
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

    public function testProcessCustomerWithIncorrectRegion()
    {
        $regionCombinedCode = '81';
        /** @var Customer $customer */
        $customer = $this->getReference('customer');
        /**
         * Set region that not in ORO list
         * @var $address Address
         */
        $regionForOriginalCustomer = new Region($regionCombinedCode);
        $address = $customer->getAddresses()->first();
        $address->setRegion($regionForOriginalCustomer);

        $resultCustomer = $this->strategy->process($customer);
        $this->assertNotEmpty(
            $resultCustomer,
            "Some error occurs, please check errors to fix the test !"
        );
        $this->assertEmpty(
            $this->stepExecution->getErrors(),
            "There are errors in context, please check !"
        );
    }

    public function testProcessWhenExistedCustomerWithNewEmailAndWebsiteIsProcessed()
    {
        /** @var Customer $originalCustomer */
        $originalCustomer = $this->getReference('customer');

        $newCustomer = new Customer();
        $newCustomer->setOriginId($originalCustomer->getOriginId());
        $newCustomer->setChannel($originalCustomer->getChannel());
        $newCustomer->setEmail('new_email@test.com');
        $newCustomer->setWebsite($this->getReference('magento.website2'));

        $resultCustomer = $this->strategy->process($newCustomer);
        $this->assertSame($newCustomer->getId(), $resultCustomer->getId());
        $this->assertSame($newCustomer->getEmail(), $resultCustomer->getEmail());
        $this->assertSame($newCustomer->getWebsite(), $resultCustomer->getWebsite());
    }
}
