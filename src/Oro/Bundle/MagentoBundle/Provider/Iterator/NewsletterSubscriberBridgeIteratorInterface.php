<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator;

interface NewsletterSubscriberBridgeIteratorInterface
{
    /**
     * @param int $initialId
     *
     * @return NewsletterSubscriberBridgeIteratorInterface
     */
    public function setInitialId($initialId);
}
