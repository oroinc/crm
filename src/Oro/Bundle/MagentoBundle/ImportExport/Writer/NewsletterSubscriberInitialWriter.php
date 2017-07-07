<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Writer;

use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class NewsletterSubscriberInitialWriter extends ProxyEntityWriter
{
    /**
     * @param NewsletterSubscriber[] $items
     */
    public function write(array $items)
    {
        parent::write($items);

        $count = count($items);
        if (!$count) {
            return;
        }
        // Save minimum originId received by initial sync for further filtering in case of failure
        $lastSubscriber = $items[$count - 1];
        $transport = $lastSubscriber->getChannel()->getTransport();
        if ($transport instanceof MagentoTransport) {
            /** @var MagentoTransport $transport */
            $transport = $this->databaseHelper->getEntityReference($transport);
            $syncedToId = $transport->getNewsletterSubscriberSyncedToId();
            if (!$syncedToId || $syncedToId > $lastSubscriber->getOriginId()) {
                $transport->setNewsletterSubscriberSyncedToId($lastSubscriber->getOriginId());
                $this->writer->write([$transport]);
            }
        }
    }
}
