<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Rest;

use Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableIterator;

class BaseMagentoRestIterator extends AbstractPageableIterator
{
    const COUNT_KEY = 'totalCount';
    const DATA_KEY = 'items';

    public function getEntityIds()
    {
        return [];
    }

    public function getEntity($id)
    {
    }

    public function getIdFieldName()
    {
        return 'id';
    }
}
