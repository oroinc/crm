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
     * @param string $objectName
     * @param $object
     *
     * @return mixed
     */
    abstract public function save($objectName, $object);

    /**
     * Insert or update record
     *
     * @param string $objectName
     * @param int $id
     *
     * @return mixed
     */
    abstract public function delete($objectName, $id);

    /**
     * Search query with query builder
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     *
     * @return \Oro\Bundle\SearchBundle\Query\Result\Item[]
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
        $result = new Result($query, $this->doSearch($query));
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
