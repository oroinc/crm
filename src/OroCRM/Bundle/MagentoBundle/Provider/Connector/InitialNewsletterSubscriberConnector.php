<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Connector;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\NewsletterSubscriberBridgeIterator;

class InitialNewsletterSubscriberConnector extends AbstractInitialConnector
{
    const TYPE = 'newsletter_subscriber_initial';
    const IMPORT_JOB_NAME = 'mage_newsletter_subscriber_import_initial';

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
        return self::IMPORT_JOB_NAME;
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

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);
        /** @var NewsletterSubscriberBridgeIterator $iterator */
        $iterator = $this->getSourceIterator();
        $iterator->setInitialId($context->getOption('initial_id'));
    }
}
