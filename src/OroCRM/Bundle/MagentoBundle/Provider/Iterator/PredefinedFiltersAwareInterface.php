<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\BatchFilterBag;

interface PredefinedFiltersAwareInterface
{
    /**
     * Set filter bag that will be used for batch processing
     *
     * @param BatchFilterBag $bag
     */
    public function setPredefinedFiltersBag(BatchFilterBag $bag);
}
