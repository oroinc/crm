<?php

namespace Oro\Bundle\SearchBundle\Engine;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Parser;

class Indexer
{
    const TEXT_ALL_DATA_FIELD = 'all_text';

    const RELATION_ONE_TO_ONE = 'one-to-one';
    const RELATION_MANY_TO_MANY = 'many-to-many';
    const RELATION_MANY_TO_ONE = 'many-to-one';
    const RELATION_ONE_TO_MANY = 'one-to-many';

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
            ->andWhere(self::TEXT_ALL_DATA_FIELD, '~', $searchString, 'text');

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

    /**
     * Advanced search from API
     *
     * @param string $searchString
     *
     * @return \Oro\Bundle\SearchBundle\Query\Result
     */
    public function advancedSearch($searchString)
    {
        $parser = new Parser($this->mappingConfig);

        return $this->query($parser->getQueryFromString($searchString));
    }
}
