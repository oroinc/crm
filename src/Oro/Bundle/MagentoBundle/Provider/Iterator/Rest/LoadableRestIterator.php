<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Rest;

use Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableIterator;

class LoadableRestIterator extends AbstractLoadeableIterator
{
    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
