<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\BaseStrategy;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

abstract class AbstractInfoReader extends AbstractReader
{
    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var Channel */
    protected $channel;

    /** @var LoggerStrategy */
    protected $logger;

    /** @var ConnectorContextMediator */
    protected $contextMediator;

    /** @var string */
    protected $className;

    /** @var bool[] */
    protected $loaded = [];

    /** @var array */
    protected $ids = [];

    /** @param string $className */
    public function setClassName($className)
    {
        $this->className = $className;
    }

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
        if ($this->transport->isExtensionInstalled()) {
            return null;
        }

        if (!$this->className) {
            throw new \InvalidArgumentException('ClassName is missing');
        }

        $entitiesIds = $this->stepExecution->getJobExecution()
            ->getExecutionContext()
            ->get(BaseStrategy::CONTEXT_POST_PROCESS_IDS);

        if (empty($entitiesIds[$this->className])) {
            return;
        }

        $entitiesIds = $entitiesIds[$this->className];
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

        $this->logger->info(sprintf('Loading entity info by id: %s', $originId));
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
