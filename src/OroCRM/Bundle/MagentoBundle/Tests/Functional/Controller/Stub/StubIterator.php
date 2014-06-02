<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller\Stub;

use OroCRM\Bundle\MagentoBundle\Provider\BatchFilterBag;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\PredefinedFiltersAwareInterface;

class StubIterator extends \ArrayIterator implements PredefinedFiltersAwareInterface
{
    /**
     * Set filter bag that will be used for batch processing
     *
     * @param BatchFilterBag $bag
     */
    public function setPredefinedFiltersBag(BatchFilterBag $bag)
    {
        // TODO: Implement setPredefinedFiltersBag() method.
    }
}
