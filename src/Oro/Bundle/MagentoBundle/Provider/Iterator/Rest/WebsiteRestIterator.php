<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Rest;

class WebsiteRestIterator extends AbstractLoadeableRestIterator
{
    /**
     * @return array
     */
    protected function getData()
    {
        return $this->transport->doGetWebsitesRequest();
    }
}
