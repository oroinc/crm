<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\CreditMemoItem;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\CreditMemoWithExistingOrderStrategy;
use Oro\Bundle\MagentoBundle\Provider\Reader\ContextOrderReader;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CreditMemoWithExistingOrderStrategyTest extends WebTestCase
{
    /**
     * @var CreditMemoWithExistingOrderStrategy
     */
    protected $strategy;

    /**
     * @var Channel
     */
    protected $channel;

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

        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel']);

        $this->strategy = $this->getContainer()
            ->get('oro_magento.import.strategy.credit_memo_with_order.add_or_update');

        $this->channel = $this->getReference('integration');
        $jobInstance = new JobInstance();
        $jobInstance->setRawConfiguration(['channel' => $this->channel->getId()]);
        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);
        $this->stepExecution = new StepExecution('step', $jobExecution);
        $this->context = new StepExecutionProxyContext($this->stepExecution);
        $this->strategy->setImportExportContext($this->context);
        $this->strategy->setStepExecution($this->stepExecution);
    }

    public function testProcessWithExistingOrder()
    {
        $creditMemo = new CreditMemo();
        $creditMemo->setIncrementId('100000002');
        $creditMemo->setChannel($this->channel);
        $order = new Order();
        $order->setOriginId(1);
        $creditMemo->setOrder($order);
        $item = new CreditMemoItem();
        $item->setQty(1);
        $creditMemo->setItems([$item]);

        $this->strategy->setEntityName(CreditMemo::class);
        $this->assertSame($creditMemo, $this->strategy->process($creditMemo));
        $this->assertInstanceOf('DateTime', $creditMemo->getImportedAt());
        $this->assertInstanceOf('DateTime', $creditMemo->getSyncedAt());

        $this->assertSame($creditMemo, $item->getParent());
        $this->assertSame($creditMemo->getOrganization(), $item->getOwner());
    }

    public function testProcessWithNonExistingOrder()
    {
        $creditMemo = new CreditMemo();
        $creditMemo->setChannel($this->channel);
        $order = new Order();
        $order->setOriginId(123123);
        $creditMemo->setOrder($order);

        $this->strategy->setEntityName(CreditMemo::class);

        $context = $this->stepExecution->getJobExecution()->getExecutionContext();
        $this->assertNull($this->strategy->process($creditMemo));
        $this->assertEquals(
            [123123],
            $context->get(ContextOrderReader::CONTEXT_POST_PROCESS_ORDERS)
        );
        $keys = $context->getKeys();
        $this->assertContains(CreditMemoWithExistingOrderStrategy::CONTEXT_CREDIT_MEMO_POST_PROCESS, $keys);
    }
}
