<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Rest;

use Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableIterator;
use Oro\Bundle\MagentoBundle\Provider\Transport\RestTransportInterface;

abstract class AbstractLoadeableRestIterator extends AbstractLoadeableIterator
{
    /** @var RestTransportInterface */
    protected $transport;

    /**
     * @param RestTransportInterface $transport
     */
    public function __construct(RestTransportInterface $transport)
    {
        $this->transport = $transport;
    }
}
