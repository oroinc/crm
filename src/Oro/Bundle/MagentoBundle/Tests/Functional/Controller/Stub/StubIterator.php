<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller\Stub;

use Oro\Bundle\MagentoBundle\Provider\BatchFilterBag;
use Oro\Bundle\MagentoBundle\Provider\Iterator\PredefinedFiltersAwareInterface;

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
