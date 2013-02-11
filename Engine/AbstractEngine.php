<?php

namespace Oro\Bundle\SearchBundle\Engine;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SearchBundle\Entity\Query as QueryLog;

/**
 * Connector abstract class
 */
abstract class AbstractEngine
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;

    /**
     * Init entity manager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * Insert or update record
     *
     * @param object $entity
     * @param bool $realtime
     *
     * @return mixed
     */
    abstract public function save($entity, $realtime = true);

    /**
     * Insert or update record
     *
     * @param object $entity
     * @param bool $realtime
     *
     * @return mixed
     */
    abstract public function delete($entity, $realtime = true);

    /**
     * Search query with query builder
     * Must return array
     * array(
     *   'results' - array of Oro\Bundle\SearchBundle\Query\Result\Item objects
     *   'records_count' - count of records without limit parameters in query
     * )
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     *
     * @return array
     */
    abstract protected function doSearch(Query $query);

    /**
     * Search query with query builder
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     *
     * @return \Oro\Bundle\SearchBundle\Query\Result
     */
    public function search(Query $query)
    {
        $searchResult = $this->doSearch($query);
        $result = new Result($query, $searchResult['results'], $searchResult['records_count']);
        $this->logQuery($result);
        return $result;
    }

    /**
     * Log query
     *
     * @param \Oro\Bundle\SearchBundle\Query\Result $result
     */
    protected function logQuery(Result $result)
    {
        $logRecord = new QueryLog;
        $logRecord->setEntity(serialize($result->getQuery()->getFrom()));
        $logRecord->setQuery(serialize($result->getQuery()->getOptions()));
        $logRecord->setResultCount($result->count());

        $this->em->persist($logRecord);
        $this->em->flush();
    }
}
