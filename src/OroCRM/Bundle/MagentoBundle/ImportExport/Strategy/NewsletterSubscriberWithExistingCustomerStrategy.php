<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use OroCRM\Bundle\MagentoBundle\Provider\Reader\ContextCustomerReader;

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
        $customer = $this->databaseHelper->findOneByIdentity($newsletterSubscriber->getCustomer());
        $isProcessingAllowed = true;

        $customerOriginId = $newsletterSubscriber->getCustomer()->getOriginId();
        if (!$customer && $customerOriginId) {
            $this->appendDataToContext(ContextCustomerReader::CONTEXT_POST_PROCESS_CUSTOMERS, $customerOriginId);

            $isProcessingAllowed = false;
        }

        return $isProcessingAllowed;
    }
}
