<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use Oro\Bundle\MagentoBundle\Provider\Reader\ContextCustomerReader;

class NewsletterSubscriberWithExistingCustomerStrategy extends NewsletterSubscriberStrategy
{
    const NEWSLETTER_SUBSCRIBER_POST_PROCESS = 'postProcessSubscribers';

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        if (!$this->isProcessingAllowed($entity)) {
            $this->appendDataToContext(self::NEWSLETTER_SUBSCRIBER_POST_PROCESS, $this->context->getValue('itemData'));

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
        $customer = null;
        $customerOriginId = null;
        $isProcessingAllowed = true;

        if ($newsletterSubscriber->getCustomer()) {
            $customerOriginId = $newsletterSubscriber->getCustomer()->getOriginId();
            $customer = $this->databaseHelper->findOneByIdentity($newsletterSubscriber->getCustomer());
        }

        if (!$customer && $customerOriginId) {
            $this->appendDataToContext(ContextCustomerReader::CONTEXT_POST_PROCESS_CUSTOMERS, $customerOriginId);

            $isProcessingAllowed = false;
        }

        return $isProcessingAllowed;
    }
}
