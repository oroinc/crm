<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\NewsletterSubscriberWithExistingCustomerStrategy;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class NewsletterSubscriberWithExistingCustomerStrategyTest extends WebTestCase
{
    /**
     * @var NewsletterSubscriberWithExistingCustomerStrategy
     */
    protected $strategy;

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

        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadNewsletterSubscriberData']);

        $this->strategy = $this->getContainer()
            ->get('oro_magento.import_strategy.newsletter_subscriber_with_customer.add_or_update');

        $this->stepExecution = new StepExecution('step', new JobExecution());
        $this->context = new StepExecutionProxyContext($this->stepExecution);
        $this->context->setValue('itemData', ['itemProp' => 'itemValue']);
        $this->strategy->setImportExportContext($this->context);
        $this->strategy->setStepExecution($this->stepExecution);
    }

    public function testProcessWithCustomer()
    {
        /** @var NewsletterSubscriber $newsletterSubscriber */
        $newsletterSubscriber = $this->getReference('newsletter_subscriber');

        $this->strategy->setEntityName(get_class($newsletterSubscriber));

        $this->assertEquals($newsletterSubscriber, $this->strategy->process($newsletterSubscriber));

        $this->assertEmpty(
            $this->stepExecution->getJobExecution()->getExecutionContext()->get('postProcessSubscribers')
        );

        $this->assertEmpty(
            $this->stepExecution->getJobExecution()->getExecutionContext()->get('postProcessCustomerIds')
        );
    }

    public function testProcessLoadCustomer()
    {
        /** @var NewsletterSubscriber $newsletterSubscriber */
        $newsletterSubscriber = $this->getReference('newsletter_subscriber2');
        $customer = new Customer();
        $originId = time();
        $customer->setOriginId($originId);
        $newsletterSubscriber->setCustomer($customer);

        $this->strategy->setEntityName(get_class($newsletterSubscriber));

        $this->assertEquals(null, $this->strategy->process($newsletterSubscriber));

        $this->assertNotEmpty(
            $this->stepExecution->getJobExecution()->getExecutionContext()->get('postProcessSubscribers')
        );

        $this->assertEquals(
            [$originId],
            $this->stepExecution->getJobExecution()->getExecutionContext()->get('postProcessCustomerIds')
        );
    }

    public function testProcessWithoutCustomer()
    {
        /** @var NewsletterSubscriber $newsletterSubscriber */
        $newsletterSubscriber = $this->getReference('newsletter_subscriber2');
        $newsletterSubscriber->setCustomer(null);
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($newsletterSubscriber);
        $em->flush($newsletterSubscriber);

        $this->strategy->setEntityName(get_class($newsletterSubscriber));

        $this->assertEquals($newsletterSubscriber, $this->strategy->process($newsletterSubscriber));

        $this->assertEmpty(
            $this->stepExecution->getJobExecution()->getExecutionContext()->get('postProcessSubscribers')
        );

        $this->assertEmpty(
            $this->stepExecution->getJobExecution()->getExecutionContext()->get('postProcessCustomerIds')
        );
    }
}
