<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutionRestoreInterface;
use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\GuestCustomerStrategyHelper;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Prepares items for PersistentBatchWriter, filters for duplicates and takes only latest versions.
 */
class ProxyEntityWriter implements
    ItemWriterInterface,
    StepExecutionAwareInterface,
    StepExecutionRestoreInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ItemWriterInterface */
    protected $writer;

    /** @var DatabaseHelper */
    protected $databaseHelper;

    /** @var StepExecution|null */
    protected $previousStepExecution;

    /** @var  GuestCustomerStrategyHelper */
    private $guestCustomerStrategyHelper;

    /**
     * @param ItemWriterInterface $writer
     * @param DatabaseHelper $databaseHelper
     */
    public function __construct(ItemWriterInterface $writer, DatabaseHelper $databaseHelper)
    {
        $this->writer = $writer;
        $this->databaseHelper = $databaseHelper;
        $this->logger = new NullLogger();
    }

    /**
     * @param GuestCustomerStrategyHelper $strategyHelper
     */
    public function setGuestCustomerStrategyHelper(GuestCustomerStrategyHelper $strategyHelper)
    {
        $this->guestCustomerStrategyHelper = $strategyHelper;
    }

    /**
     * {@inheritdoc}
     *
     * Prepare items for PersistentBatchWriter, filters for duplicates and takes only latest versions
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function write(array $items)
    {
        $uniqueItems = [];
        $uniqueKeys = [];
        foreach ($items as $item) {
            if ($item instanceof Customer) {
                //GuestCustomerStrategy checks both email and channel
                if ($item->isGuest()) {
                    $identifier = $this->getGuestCustomerIdentifier($item);
                } else {
                    $identifier = $item->getOriginId();
                }
                $this->handleIdentifier($uniqueItems, $item, $identifier);
            } elseif ($item instanceof Cart) {
                $this->handleIdentifier($uniqueItems, $item, $item->getOriginId());
            } elseif ($item instanceof Order) {
                $this->handleIdentifier($uniqueItems, $item, $item->getIncrementId());
            } elseif ($item instanceof CreditMemo) {
                $this->handleIdentifier($uniqueItems, $item, $item->getOriginId());
            } elseif ($item instanceof NewsletterSubscriber) {
                $identifier = $item->getCustomer() ? $item->getCustomer()->getId() : 0;
                if ($identifier !== 0 && in_array($identifier, $uniqueKeys)) {
                    $this->logSkipped($item->getOriginId());
                } else {
                    $uniqueKeys[] = $identifier;
                    $uniqueItems[] = $item;
                }
            } else {
                $uniqueItems[] = $item;
            }
        }

        $this->writer->write($uniqueItems);

        // force entity cache clear if clear is skipped
        $this->databaseHelper->onClear();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        if ($this->writer instanceof StepExecutionAwareInterface) {
            $this->writer->setStepExecution($stepExecution);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function restoreStepExecution()
    {
        if ($this->writer instanceof StepExecutionRestoreInterface) {
            $this->writer->restoreStepExecution();
        }
    }

    /**
     * @param array $uniqueItems
     * @param object $item
     * @param string|null $identifier
     */
    protected function handleIdentifier(array &$uniqueItems, $item, $identifier = null)
    {
        if ($identifier && array_key_exists($identifier, $uniqueItems)) {
            $this->logSkipped($identifier);
        }

        if ($identifier) {
            $uniqueItems[$identifier] = $item;
        } else {
            $uniqueItems[spl_object_hash($item)] = $item;
        }
    }

    /**
     * @param int|string $identifier
     */
    protected function logSkipped($identifier)
    {
        $this->logger->info(
            sprintf('[origin_id=%s] Item skipped because of newer version found', (string)$identifier)
        );
    }

    /**
     * @param Customer $customer
     *
     * @return string
     */
    private function getGuestCustomerIdentifier(Customer $customer)
    {
        $channel = $customer->getChannel();
        $identifier = strtolower($customer->getEmail());
        //set unique identifier: email and channel id
        if ($channel) {
            $identifier .= $channel->getId();
            if ($this->guestCustomerStrategyHelper->isGuestCustomerEmailInSharedList($customer)) {
                $identifier .= sprintf('%s%s', $customer->getFirstName(), $customer->getLastName());
            }
        }

        $identifier = md5($identifier);

        return $identifier;
    }
}
