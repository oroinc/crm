<?php

namespace Oro\Bundle\SearchBundle\Query;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

use Oro\Bundle\SearchBundle\Query\Query;

class Result extends ArrayCollection
{
    /**
     * @Type("Oro\Bundle\SearchBundle\Query\Query")
     * @Exclude
     */
    protected $query;

    /**
     * @var integer
     */
    protected $recordsCount;

    /**
     * Initializes a new Result.
     *
     * @param Query $query
     * @param array $elements
     * @param integer $recordsCount
     */
    public function __construct(Query $query, array $elements = array(), $recordsCount = 0)
    {
        $this->query = $query;
        $this->recordsCount = $recordsCount;
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

    /**
     * Return number of records of search query without limit parameters
     *
     * @return int
     */
    public function getRecordsCount()
    {
        return $this->recordsCount;
    }

    /**
     * Gets the PHP array representation of this collection.
     * @return array
     */
    public function toSearchResultData()
    {
        $resultData['records_count'] = $this->recordsCount;
        if ($this->count()) {
            $resultData['count'] = $this->count();
            $resultData['data'] = $this->toArray();
        } else {
            $resultData['count'] = 0;
        }

        return $resultData;
    }
}
