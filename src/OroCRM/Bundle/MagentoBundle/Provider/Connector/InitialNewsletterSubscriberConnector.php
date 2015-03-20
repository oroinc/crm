<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Connector;

use OroCRM\Bundle\MagentoBundle\Provider\NewsletterSubscriberConnector;

class InitialNewsletterSubscriberConnector extends AbstractInitialConnector
{
    const TYPE = 'newsletter_subscriber_initial';

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.newsletter_subscriber.initial.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return NewsletterSubscriberConnector::IMPORT_JOB_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     * @return InitialNewsletterSubscriberConnector
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getNewsletterSubscribers();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
