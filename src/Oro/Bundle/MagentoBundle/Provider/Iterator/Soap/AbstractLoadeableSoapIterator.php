<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\MagentoBundle\Utils\WSIUtils;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransportInterface;
use Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableIterator;

abstract class AbstractLoadeableSoapIterator extends AbstractLoadeableIterator
{
    /** @var SoapTransportInterface */
    protected $transport;

    /**
     * @param SoapTransportInterface $transport
     */
    public function __construct(SoapTransportInterface $transport)
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
