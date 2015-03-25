<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

class NewsletterSubscriberConnector extends AbstractMagentoConnector
{
    const IMPORT_JOB_NAME = 'mage_newsletter_subscriber_import';
    const TYPE = 'newsletter_subscriber';

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.newsletter_subscriber.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return $this->entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return self::IMPORT_JOB_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * @param string $entityClass
     * @return NewsletterSubscriberConnector
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
}
