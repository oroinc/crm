<?php

namespace Oro\Bundle\SearchBundle\Query;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\SearchBundle\Query\Query;

class Result extends ArrayCollection
{
    protected $query;

    /**
     * Initializes a new Result.
     *
     * @param Query $query
     * @param array $elements
     */
    public function __construct(Query $query, array $elements = array())
    {
        $this->query = $query;
        parent::__construct($elements);
    }

    /**
     * get Query object
     *
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }
}
