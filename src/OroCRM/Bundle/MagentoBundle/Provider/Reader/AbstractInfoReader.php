<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
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

    /**
     * @param ContextRegistry          $contextRegistry
     * @param LoggerStrategy           $logger
     * @param ConnectorContextMediator $contextMediator
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    ) {
        parent::__construct($contextRegistry);

        $this->logger          = $logger;
        $this->contextMediator = $contextMediator;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $this->channel   = $this->contextMediator->getChannel($context);
        $this->transport = $this->contextMediator->getInitializedTransport($this->channel, true);
    }

    /**
     * @return object
     */
    protected function getData()
    {
        $configuration = $this->getContext()->getConfiguration();

        if (empty($configuration['data'])) {
            throw new \InvalidArgumentException('Data is missing');
        }

        $data = $configuration['data'];

        if (!$data instanceof $this->className) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Instance of "%s" expected, "%s" given.',
                    $this->className,
                    is_object($data) ? get_class($data) : gettype($data)
                )
            );
        }

        return $data;
    }
}
