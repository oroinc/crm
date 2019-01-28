<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableIterator;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface;
use Oro\Bundle\MagentoBundle\Utils\WSIUtils;

abstract class AbstractPageableSoapIterator extends AbstractPageableIterator
{
    /** @var MagentoSoapTransportInterface */
    protected $transport;

    /**
     * @param MagentoSoapTransportInterface $transport
     * @param array                         $settings
     */
    public function __construct(MagentoSoapTransportInterface $transport, array $settings)
    {
        parent::__construct($transport, $settings);
    }

    /**
     * @param mixed $response
     *
     * @return array
     */
    protected function processCollectionResponse($response)
    {
        return WSIUtils::processCollectionResponse($response);
    }

    /**
     * @param array $response
     *
     * @return array
     */
    protected function convertResponseToMultiArray($response)
    {
        return WSIUtils::convertResponseToMultiArray($response);
    }
}
