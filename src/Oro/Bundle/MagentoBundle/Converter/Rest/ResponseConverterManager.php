<?php

namespace Oro\Bundle\MagentoBundle\Converter\Rest;

use Oro\Bundle\MagentoBundle\Converter\RestResponseConverterInterface;

class ResponseConverterManager
{
    protected $converters = [];

    /**
     * @param array $data
     * @param string $type
     *
     * @return array
     */
    public function convert($data, $type)
    {
        if (!$type || !isset($this->converters[$type])) {
            return $data;
        }

        return $this->converters[$type]->convert($data);
    }

    /**
     * @param string $responseType
     * @param RestResponseConverterInterface $converter
     */
    public function addConverter($responseType, RestResponseConverterInterface $converter)
    {
        $this->converters[$responseType] = $converter;
    }
}
