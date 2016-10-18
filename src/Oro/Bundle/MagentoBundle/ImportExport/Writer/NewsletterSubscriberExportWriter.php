<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Writer;

class NewsletterSubscriberExportWriter extends AbstractExportWriter
{
    const CONTEXT_POST_PROCESS_KEY = 'postProcessNewsletterSubscribers';
    const SUBSCRIBER_ID_KEY = 'subscriber_id';
    const STATUS_KEY = 'subscriber_status';

    /**
     * @param array $items
     */
    public function write(array $items)
    {
        $item = reset($items);

        if (!$item) {
            $this->logger->error('Wrong Newsletter Subscriber data', (array)$item);

            return;
        }

        $statusIdentifier = $this->getStatusIdentifier();
        if ($statusIdentifier) {
            $item[self::STATUS_KEY] = $statusIdentifier;
        }

        $this->transport->init($this->getChannel()->getTransport());
        if (empty($item[self::SUBSCRIBER_ID_KEY])) {
            $this->writeNewItem($item);
        } else {
            $this->writeExistingItem($item);
        }
    }

    /**
     * @param array $item
     */
    protected function writeNewItem(array $item)
    {
        try {
            $subscriberData = $this->transport->createNewsletterSubscriber($item);

            if ($subscriberData) {
                $this->stepExecution->getJobExecution()
                    ->getExecutionContext()
                    ->put(self::CONTEXT_POST_PROCESS_KEY, [$subscriberData]);

                $this->logger->info(
                    sprintf(
                        'Newsletter Subscriber with data %s successfully created',
                        json_encode($subscriberData)
                    )
                );
            } else {
                $this->logger->error(sprintf('Newsletter Subscriber with data %s was not created', json_encode($item)));
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        }
    }

    /**
     * @param array $item
     */
    protected function writeExistingItem(array $item)
    {
        $subscriberId = $item[self::SUBSCRIBER_ID_KEY];

        try {
            $subscriberData = $this->transport->updateNewsletterSubscriber($subscriberId, $item);

            if ($subscriberData) {
                $this->stepExecution->getJobExecution()
                    ->getExecutionContext()
                    ->put(self::CONTEXT_POST_PROCESS_KEY, [$subscriberData]);

                $this->logger->info(
                    sprintf(
                        'Newsletter Subscriber with id %s and data %s successfully updated',
                        $subscriberId,
                        json_encode($subscriberData)
                    )
                );
            } else {
                $this->logger->error(
                    sprintf(
                        'Newsletter Subscriber with id %s and data %s was not updated',
                        $subscriberId,
                        json_encode($item)
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        }
    }

    /**
     * @return int
     */
    protected function getStatusIdentifier()
    {
        return (int)$this->getContext()->getOption('statusIdentifier');
    }
}
