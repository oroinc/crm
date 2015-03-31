<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\NewsletterSubscriberStrategy;

/**
 * @dbIsolation
 */
class NewsletterSubscriberStrategyTest extends WebTestCase
{
    /**
     * @var NewsletterSubscriberStrategy
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

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures(['OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadNewsletterSubscriberData']);

        $this->strategy = $this->getContainer()
            ->get('orocrm_magento.import.strategy.newsletter_subscriber.add_or_update');

        $this->stepExecution = new StepExecution('step', new JobExecution());
        $this->context = new StepExecutionProxyContext($this->stepExecution);
        $this->strategy->setImportExportContext($this->context);
        $this->strategy->setStepExecution($this->stepExecution);
    }

    public function testProcessSuccessful()
    {
        /** @var NewsletterSubscriber $newsletterSubscriber */
        $newsletterSubscriber = $this->getReference('newsletter_subscriber');
        $newsletterSubscriber->setDataChannel(null);

        $this->strategy->setEntityName(get_class($newsletterSubscriber));

        $this->assertEquals($newsletterSubscriber, $this->strategy->process($newsletterSubscriber));
        $this->assertEquals($newsletterSubscriber->getDataChannel(), $this->getReference('default_channel'));
    }
}
