<?php

namespace Oro\Bundle\SearchBundle\Engine;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SearchBundle\Query\Query;

class Indexer
{
    /**
     * @var \Oro\Bundle\SearchBundle\Engine\AbstractEngine
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $mappingConfig;

    /*
     *
    * @var \Doctrine\Common\Persistence\ObjectManager
    */
    private $em;

    public function __construct(ObjectManager $em, $adapter, $mappingConfig)
    {
        $this->mappingConfig = $mappingConfig;
        $this->adapter = $adapter;
        $this->em = $em;
    }

    /**
     * @param string  $searchString
     * @param integer $offset
     * @param integer $maxResults
     *
     * @return \Oro\Bundle\SearchBundle\Query\Result
     */
    public function simpleSearch($searchString, $offset, $maxResults)
    {
        $query =  $this->select()
            ->from('*')
            ->andWhere('*', '=', $searchString, 'text');

        if ($maxResults > 0) {
            $query->setMaxResults($maxResults);
        } else {
            $query->setMaxResults(10000000);
        }

        if ($offset > 0) {
            $query->setFirstResult($offset);
        }

        return $this->query($query);
    }

    /**
     * Get query builder with select instance
     *
     * @return \Oro\Bundle\SearchBundle\Query\Query
     */
    public function select()
    {
        $query = new Query(Query::SELECT);

        $query->setMappingConfig($this->mappingConfig);
        $query->setEntityManager($this->em);

        return $query;
    }

    /**
     * Run query with query builder
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     *
     * @return \Oro\Bundle\SearchBundle\Query\Result
     */
    public function query(Query $query)
    {
        if ($query->getQuery() == Query::SELECT) {
            return $this->adapter->search($query);
        }
    }
}
