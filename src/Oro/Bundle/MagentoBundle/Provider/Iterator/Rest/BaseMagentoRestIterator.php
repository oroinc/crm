<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Rest;

use Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableIterator;

class BaseMagentoRestIterator extends AbstractPageableIterator
{
    const COUNT_KEY = 'totalCount';
    const DATA_KEY = 'items';

    /**
     * {@inheritdoc}
     */
    public function getEntityIds()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity($id)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getIdFieldName()
    {
        return 'id';
    }
}
