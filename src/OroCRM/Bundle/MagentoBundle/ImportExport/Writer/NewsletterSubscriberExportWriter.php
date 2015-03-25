<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Writer;

class NewsletterSubscriberExportWriter extends AbstractExportWriter
{
    const CONTEXT_POST_PROCESS_KEY = 'postProcessNewsletterSubscribers';
    const SUBSCRIBER_ID_KEY = 'subscriber_id';

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

        $this->transport->init($this->getChannel()->getTransport());
        if (empty($item[self::SUBSCRIBER_ID_KEY])) {
            $this->writeNewItem($item);
        } else {
            $this->writeExistingItem($item);
        }

        parent::write([$this->getEntity()]);
    }

    /**
     * @param array $item
     */
    protected function writeNewItem(array $item)
    {
        try {
            $subscriberId = $this->transport->createNewsletterSubscriber($item);

            $this->getEntity()->setOriginId($subscriberId);

            if ($subscriberId) {
                $this->logger->info(
                    sprintf(
                        'Newsletter Subscriber with id %s successfully created with data %s',
                        $subscriberId,
                        json_encode($item)
                    )
                );
            } else {
                $this->logger->error(sprintf('Newsletter Subscriber with id %s was not updated', $subscriberId));
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
            $result = $this->transport->updateNewsletterSubscriber($subscriberId, $item);

            if ($result) {
                $this->logger->info(
                    sprintf(
                        'Newsletter Subscriber with id %s successfully updated with data %s',
                        $subscriberId,
                        json_encode($item)
                    )
                );
            } else {
                $this->logger->error(sprintf('Newsletter Subscriber with id %s was not updated', $subscriberId));
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        }
    }
}
