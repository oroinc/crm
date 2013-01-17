<?php

namespace Oro\Bundle\SearchBundle\Engine;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SearchBundle\Entity\Queue;
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