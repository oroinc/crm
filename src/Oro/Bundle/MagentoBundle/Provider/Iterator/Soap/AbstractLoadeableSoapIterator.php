<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableIterator;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface;
use Oro\Bundle\MagentoBundle\Utils\WSIUtils;

abstract class AbstractLoadeableSoapIterator extends AbstractLoadeableIterator
{
    /** @var MagentoSoapTransportInterface */
    protected $transport;

    /**
     * @param MagentoSoapTransportInterface $transport
     */
    public function __construct(MagentoSoapTransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Do modifications with response for collection requests
     * Fix issues related to specific results in WSI mode
     *
     * @param mixed $response
     *
     * @return array
     */
    protected function processCollectionResponse($response)
    {
        return WSIUtils::processCollectionResponse($response);
    }
}
