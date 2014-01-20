<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

abstract class AbstractMagentoConnector extends AbstractConnector implements MagentoConnectorInterface
{
    /** @var MagentoTransportInterface */
    protected $transport;

    /**
     * {@inheritdoc}
     */
    protected function validateConfiguration()
    {
        parent::validateConfiguration();

        if (!$this->transport instanceof MagentoTransportInterface) {
            throw new \LogicException('Option "transport" should implement "MagentoTransportInterface"');
        }
    }
}
