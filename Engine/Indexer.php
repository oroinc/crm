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
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($maxResults) {
            $query->setMaxResults($maxResults);
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
     * Delete record from search index
     *
     * @param \Oro\Bundle\SearchBundle\Entity\Queue $queue
     *
     * @return bool|array
     */
    public function delete(Queue $queue)
    {
        $result = $this->adapter->delete($queue->getEntity(), $queue->getRecordId());
        $this->em->remove($queue);
        $this->em->flush();
        if ($result) {
            return array('recordId' => $result, 'action' => Queue::EVENT_DELETE);
        }

        return false;
    }

    /**
     * Save record to search index
     *
     * @param \Oro\Bundle\SearchBundle\Entity\Queue $queue
     *
     * @return bool|array
     */
    public function save(Queue $queue)
    {
        $object = $this->em->getRepository($queue->getEntity())->find($queue->getRecordId());
        if ($object) {
            $result = $this->adapter->save($queue->getEntity(), $object);
            $this->em->remove($queue);
            $this->em->flush();

            if ($result) {
                return array('recordId' => $result, 'action' => Queue::EVENT_SAVE);
            }
        }

        return false;
    }

    /**
     * Processing queue records
     *
     * @return array
     */
    public function runQueues()
    {
        $result = array();
        $queues = $this->getQueueRepo()->findAll();
        foreach ($queues as $queue) {
            /** @var $queue \Oro\Bundle\SearchBundle\Entity\Queue */
            if ($queue->getEvent() == Queue::EVENT_SAVE) {
                if ($resultRecord = $this->save($queue)) {
                    $result[] = $resultRecord;
                }
            } else {
                if ($resultRecord = $this->delete($queue)) {
                    $result[] = $resultRecord;
                }
            }

        }

        return $result;
    }

    /**
     * Return Queue repository
     *
     * @return \Oro\Bundle\SearchBundle\Entity\Repository\QueueRepository
     */
    protected function getQueueRepo()
    {
        return $this->em->getRepository('OroSearchBundle:Queue');
    }
}
