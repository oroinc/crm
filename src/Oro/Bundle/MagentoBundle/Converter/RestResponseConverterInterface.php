<?php

namespace Oro\Bundle\MagentoBundle\Converter;

interface RestResponseConverterInterface
{
    /**
     * @param $data
     *
     * @return array
     */
    public function convert($data);
}
