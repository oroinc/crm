<?php

namespace Oro\Bundle\MagentoBundle\Converter\Rest;

use Oro\Bundle\MagentoBundle\Converter\RestResponseConverterInterface;
use Psr\Container\ContainerInterface;

/**
 * The Magento REST API response converter.
 */
class ResponseConverterManager
{
    /** @var ContainerInterface */
    private $converters;

    /**
     * @param ContainerInterface $converters
     */
    public function __construct(ContainerInterface $converters)
    {
        $this->converters = $converters;
    }

    /**
     * @param array $data
     * @param string $type
     *
     * @return array
     */
    public function convert($data, $type)
    {
        if (!$type || !$this->converters->has($type)) {
            return $data;
        }

        /** @var RestResponseConverterInterface $converter */
        $converter = $this->converters->get($type);

        return $converter->convert($data);
    }
}
