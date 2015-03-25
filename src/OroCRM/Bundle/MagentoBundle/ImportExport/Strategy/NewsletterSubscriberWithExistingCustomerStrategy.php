<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class NewsletterSubscriberWithExistingCustomerStrategy extends NewsletterSubscriberStrategy
{
    const NEWSLETTER_SUBSCRIBER_POST_PROCESS = 'postProcessSubscribers';

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        if (!$this->isProcessingAllowed($entity)) {
            $process = (array)$this->getExecutionContext()->get(self::NEWSLETTER_SUBSCRIBER_POST_PROCESS);
            $process[] = $this->context->getValue('itemData');
            $this->getExecutionContext()->put(self::NEWSLETTER_SUBSCRIBER_POST_PROCESS, $process);

            return null;
        }

        return parent::process($entity);
    }

    /**
     * @param NewsletterSubscriber $newsletterSubscriber
     * @return bool
     */
    protected function isProcessingAllowed(NewsletterSubscriber $newsletterSubscriber)
    {
        $customerId = $newsletterSubscriber->getCustomer()->getOriginId();

        if (!$customerId) {
            return true;
        }

        return (bool)$this->databaseHelper->findOneByIdentity($newsletterSubscriber->getCustomer());
    }
}
