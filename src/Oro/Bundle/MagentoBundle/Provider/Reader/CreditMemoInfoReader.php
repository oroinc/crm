<?php

namespace Oro\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

class CreditMemoInfoReader extends AbstractInfoReader
{
    /** @var bool  */
    protected $extensionUsed = false;

    /**
     * {@inheritdoc}
     */
    protected function loadEntityInfo($originId)
    {
        $this->logger->info(sprintf('Loading CreditMemo info by incrementId: %s', $originId));

        $result = $this->transport->getCreditMemoInfo($originId);

        return ConverterUtils::objectToArray($result);
    }
}
