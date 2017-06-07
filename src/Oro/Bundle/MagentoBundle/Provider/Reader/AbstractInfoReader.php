<?php

namespace Oro\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

abstract class AbstractInfoReader extends AbstractContextKeyReader
{
    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var Channel */
    protected $channel;

    /** @var LoggerStrategy */
    protected $logger;

    /** @var ConnectorContextMediator */
    protected $contextMediator;

    /** @var bool[] */
    protected $loaded = [];

    /** @var array */
    protected $ids = [];

    /**
     * Flag to control if read entity is used by the bridge extension
     *
     * @var bool
     */
    protected $extensionUsed = true;

    /**
     * @param ContextRegistry $contextRegistry
     * @param LoggerStrategy $logger
     * @param ConnectorContextMediator $contextMediator
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    ) {
        parent::__construct($contextRegistry);

        $this->logger = $logger;
        $this->contextMediator = $contextMediator;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $this->channel = $this->contextMediator->getChannel($context);
        $this->transport = $this->contextMediator->getInitializedTransport($this->channel, true);

        // info was loaded from index action
        if ($this->extensionUsed && $this->transport->isSupportedExtensionVersion()) {
            return;
        }

        $entitiesIds = (array)$this->stepExecution->getJobExecution()
            ->getExecutionContext()
            ->get($this->contextKey);

        if (!$entitiesIds) {
            return;
        }

        sort($entitiesIds);
        $this->ids = array_unique(array_filter($entitiesIds));
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->ids) {
            return null;
        }

        $originId = array_shift($this->ids);
        if (!$originId) {
            return null;
        }

        $data = null;

        try {
            $data = $this->loadEntityInfo($originId);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Loading entity info by id %s failed with error: ', $originId, $e->getMessage())
            );

            // read next record
            return $this->read();
        }

        return $data;
    }

    /**
     * @param string $originId
     * @return array|null
     */
    abstract protected function loadEntityInfo($originId);
}
