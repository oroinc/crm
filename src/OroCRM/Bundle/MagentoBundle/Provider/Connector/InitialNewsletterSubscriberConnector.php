<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Connector;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

use OroCRM\Bundle\MagentoBundle\Provider\AbstractMagentoConnector;
use OroCRM\Bundle\MagentoBundle\Provider\ExtensionVersionAwareInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\NewsletterSubscriberBridgeIterator;

class InitialNewsletterSubscriberConnector extends AbstractMagentoConnector implements
    ExtensionVersionAwareInterface,
    InitialConnectorInterface
{
    const TYPE = 'newsletter_subscriber_initial';
    const IMPORT_JOB_NAME = 'mage_newsletter_subscriber_import_initial';

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
        $iterator = $this->getSourceIterator();
        if ($iterator instanceof NewsletterSubscriberBridgeIterator) {
            $iterator->setInitialId($context->getOption('initial_id'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsForceSync()
    {
        return true;
    }
}
