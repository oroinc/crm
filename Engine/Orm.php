<?php

namespace Oro\Bundle\SearchBundle\Engine;

use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Util\PropertyPath;

use JMS\JobQueueBundle\Entity\Job;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result\Item as ResultItem;
use Oro\Bundle\SearchBundle\Entity\Item;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

class Orm extends AbstractEngine
{
    /**
     * @var array
     */
    protected $mappingConfig;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Oro\Bundle\SearchBundle\Entity\Repository\SearchIndexRepository
     */
    protected $searchRepo;

    /**
     * @var \JMS\JobQueueBundle\Entity\Repository\JobRepository
     */
    protected $jobRepo;

    public function __construct(EntityManager $em, ContainerInterface $container, $mappingConfig, $logQueries)
    {
        $this->container = $container;
        $this->em = $em;
        $this->mappingConfig = $mappingConfig;
        $this->logQueries = $logQueries;

        //todo: set translated mappingConfig only once
        $translator = $container->get('translator');
        foreach ($this->mappingConfig as $entity => $config) {
            if (isset($this->mappingConfig[$entity]['label'])) {
                $this->mappingConfig[$entity]['label'] = $translator->trans($config['label']);
            }
        }
    }

    /**
     * Reload search index
     *
     * @return int Count of index records
     */
    public function reindex()
    {
        //clear old index
        $this->clearSearchIndex();

        //index data by mapping config
        $recordsCount = 0;
        foreach ($this->mappingConfig as $entityName => $mappingConfig) {
            $entityData = $this->em->getRepository($entityName)->findAll();
            foreach ($entityData as $entity) {
                if ($this->save($entity, true) !== false) {
                    $recordsCount++;
                }
            }
        }

        return $recordsCount;
    }

    /**
     * Delete record from index
     *
     * @param object $entity   Entity to be removed from index
     * @param bool   $realtime [optional] Perform immediate insert/update to
     *                              search attributes table(s). True by default.
     * @return bool|int Index item id on success, false otherwise
     */
    public function delete($entity, $realtime = true)
    {
        $item = $this->getIndexRepo()->findOneBy(
            array(
                'entity'   => get_class($entity),
                'recordId' => $entity->getId()
            )
        );

        if ($item) {
            $id = $item->getId();

            if ($realtime) {
                $this->em->remove($item);
            } else {
                $item->setChanged(!$realtime);

                $this->reindexJob();
            }

            $this->em->flush();

            return $id;
        }

        return false;
    }

    /**
     * Insert or update record
     *
     * @param object $entity   New/updated entity
     * @param bool   $realtime [optional] Perform immediate insert/update to
     *                              search attributes table(s). True by default.
     * @return bool|int Index item id on success, false otherwise
     */
    public function save($entity, $realtime = true)
    {
        $data = $this->mapObject($entity);
        $name = get_class($entity);

        if (count($data)) {
            $item = $this->getIndexRepo()->findOneBy(
                array(
                    'entity'   => $name,
                    'recordId' => $entity->getId()
                )
            );

            if (!$item) {
                $item = new Item();

                if (isset($this->mappingConfig[get_class($entity)]['alias'])) {
                    $alias = $this->mappingConfig[get_class($entity)]['alias'];
                } else {
                    $alias = get_class($entity);
                }

                $item->setEntity($name)
                     ->setRecordId($entity->getId())
                     ->setAlias($alias);
            }

            $item->setChanged(!$realtime);

            if ($realtime) {
                $item->setTitle($this->getEntityTitle($entity))
                    ->saveItemData($data);
            } else {
                $this->reindexJob();
            }

            $this->em->persist($item);
            $this->em->flush();

            return $item->getId();
        }

        return false;
    }

    /**
     * Map object data for index
     *
     * @param object $object
     *
     * @return array
     */
    public function mapObject($object)
    {
        $mappingConfig = $this->mappingConfig;
        $objectData = array();

        if (is_object($object) && isset($mappingConfig[get_class($object)])) {
            $config = $mappingConfig[get_class($object)];
            if (isset($config['alias'])) {
                $alias = $config['alias'];
            } else {
                $alias = get_class($object);
            }
            foreach ($config['fields'] as $field) {
                // check field relation type and set it to null if field doesn't have relations
                if (!isset($field['relation_type'])) {
                    $field['relation_type'] = 'none';
                }

                $value = $this->getFieldValue($object, $field['name']);

                switch ($field['relation_type']) {
                    case Indexer::RELATION_ONE_TO_ONE:
                    case Indexer::RELATION_MANY_TO_ONE:
                        $objectData = $this->setRelatedFields($alias, $objectData, $field['relation_fields'], $value, $field['name']);

                        break;
                    case Indexer::RELATION_MANY_TO_MANY:
                    case Indexer::RELATION_ONE_TO_MANY:
                        foreach ($value as $relationObject) {
                            $objectData = $this->setRelatedFields($alias, $objectData, $field['relation_fields'], $relationObject, $field['name']);
                        }

                        break;
                    default:
                        if ($value) {
                            $objectData = $this->setDataValue($alias, $objectData, $field, $value);
                        }
                }
            }
            if (isset($config['flexible_manager'])) {
                $objectData =  $this->setFlexibleFields($alias, $object, $objectData, $config['flexible_manager']);
            }
        }

        return $objectData;
    }

    /**
     * Search query with query builder
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     *
     * @return array
     */
    protected function doSearch(Query $query)
    {
        $results = array();
        $searchResults = $this->getIndexRepo()->search($query);
        if (($query->getMaxResults() > 0 || $query->getFirstResult() > 0)) {
            $recordsCount = $this->getIndexRepo()->getRecordsCount($query);
        } else {
            $recordsCount = count($searchResults);
        }
        if ($searchResults) {
            foreach ($searchResults as $item) {
                if (is_array($item)) {
                    $item = $item['item'];
                }
                /** @var $item \Oro\Bundle\SearchBundle\Entity\Item  */
                $results[] = new ResultItem(
                    $this->em,
                    $item->getEntity(),
                    $item->getRecordId(),
                    $item->getTitle(),
                    $this->getEntityUrl(
                        $this->em->getRepository($item->getEntity())->find($item->getRecordId())
                    ),
                    $item->getRecordText(),
                    $this->mappingConfig[$item->getEntity()]
                );
            }
        }

        return array(
            'results' => $results,
            'records_count' => $recordsCount
        );
    }

    /**
     * Map Flexible entity fields
     *
     * @param string $alias
     * @param $object
     * @param array  $objectData
     * @param string $managerName
     *
     * @return array
     */
    protected function setFlexibleFields($alias, $object, $objectData, $managerName)
    {
        /** @var $flexibleManager \Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager */
        $flexibleManager = $this->container->get($managerName);
        if ($flexibleManager) {
            $attributes = $flexibleManager->getAttributeRepository()
                ->findBy(array('entityType' => $flexibleManager->getFlexibleName()));
            if (count($attributes)) {
                /** @var $attribute \Oro\Bundle\FlexibleEntityBundle\Entity\Attribute */
                foreach ($attributes as $attribute) {
                    if ($attribute->getSearchable()) {
                        $value = $object->getValue($attribute->getCode());
                        if ($value) {
                            $attributeType = $attribute->getBackendType();

                            switch ($attributeType) {
                                case AbstractAttributeType::BACKEND_TYPE_TEXT:
                                case AbstractAttributeType::BACKEND_TYPE_VARCHAR:
                                    $objectData = $this->saveFlexibleTextData(
                                        $alias,
                                        $objectData,
                                        $attribute->getCode(),
                                        $value->__toString()
                                    );
                                    break;
                                case AbstractAttributeType::BACKEND_TYPE_DATETIME:
                                case AbstractAttributeType::BACKEND_TYPE_DATE:
                                    $objectData = $this->saveFlexibleData(
                                        $alias,
                                        $objectData,
                                        AbstractAttributeType::BACKEND_TYPE_DATETIME,
                                        $attribute->getCode(),
                                        $value->getData()
                                    );
                                    break;
                                default:
                                    $objectData = $this->saveFlexibleData(
                                        $alias,
                                        $objectData,
                                        $attributeType,
                                        $attribute->getCode(),
                                        $value->__toString()
                                    );
                            }
                        }
                    }
                }
            }
        }

        return $objectData;
    }

    /**
     * @param string $alias
     * @param array  $objectData
     * @param string $attributeType
     * @param string $attribute
     * @param mixed  $value
     *
     * @return array
     */
    protected function saveFlexibleData($alias, $objectData, $attributeType, $attribute, $value)
    {
        if ($attributeType != AbstractAttributeType::BACKEND_TYPE_OPTION) {
            $objectData[$attributeType][$attribute] = $value;
        }
        //$objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][$alias . '_' . $attribute] = $value;
        return $objectData;
    }

    /**
     * @param string $alias
     * @param array  $objectData
     * @param string $attribute
     * @param mixed  $value
     *
     * @return array
     */
    protected function saveFlexibleTextData($alias, $objectData, $attribute, $value)
    {
        if (!isset($objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][$attribute])) {
            $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][$attribute] = '';
        }
        $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][$attribute] .= " " . $value;
        if (!isset($objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][Indexer::TEXT_ALL_DATA_FIELD])) {
            $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][Indexer::TEXT_ALL_DATA_FIELD] = '';
        }
        $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][Indexer::TEXT_ALL_DATA_FIELD] .= " " . $value;
        $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][$alias . '_' . $attribute] = $value;

        return $objectData;
    }

    /**
     * Set related fields values
     *
     * @param string $alias
     * @param array  $objectData
     * @param array  $relationFields
     * @param object $relationObject
     * @param string $parentName
     *
     * @return array
     */
    protected function setRelatedFields($alias, $objectData, $relationFields, $relationObject, $parentName)
    {
        foreach ($relationFields as $relationObjectField) {
            $value = $this->getFieldValue($relationObject, $relationObjectField['name']);
            if ($value) {
                $relationObjectField['name'] = $parentName;
                $objectData = $this->setDataValue(
                    $alias,
                    $objectData,
                    $relationObjectField,
                    $value
                );
            }
        }

        return $objectData;
    }

    /**
     * @param object|array $objectOrArray
     * @param string       $fieldName
     *
     * @return mixed
     */
    protected function getFieldValue($objectOrArray, $fieldName)
    {
        $propertyPath = new PropertyPath($fieldName);

        return $propertyPath->getValue($objectOrArray);
    }

    /**
     * Set value for meta fields by type
     *
     * @param string $alias
     * @param array  $objectData
     * @param array  $fieldConfig
     * @param mixed  $value
     *
     * @return array
     */
    protected function setDataValue($alias, $objectData, $fieldConfig, $value)
    {
        //check if field have target_fields parameter
        if (isset($fieldConfig['target_fields']) && count($fieldConfig['target_fields'])) {
            $targetFields = $fieldConfig['target_fields'];
        } else {
            $targetFields = array($fieldConfig['name']);
        }

        if ($fieldConfig['target_type'] != 'text') {
            foreach ($targetFields as $targetField) {
                $objectData[$fieldConfig['target_type']][$targetField] = $value;
            }

        } else {
            foreach ($targetFields as $targetField) {
                if (!isset($objectData[$fieldConfig['target_type']][$targetField])) {
                    $objectData[$fieldConfig['target_type']][$targetField] = '';
                }
                $objectData[$fieldConfig['target_type']][$targetField] .= $value . ' ';
            }
            if (!isset($objectData[$fieldConfig['target_type']][Indexer::TEXT_ALL_DATA_FIELD])) {
                $objectData[$fieldConfig['target_type']][Indexer::TEXT_ALL_DATA_FIELD] = '';
            }
            $objectData[$fieldConfig['target_type']][Indexer::TEXT_ALL_DATA_FIELD] .= $value . ' ';
        }

        return $objectData;
    }

    /**
     * Add reindex task to job queue if it has not been added earlier
     */
    protected function reindexJob()
    {
        // check if reindex task has not been added earlier
        $command = 'oro:search:index';
        $currJob = $this->em->createQuery("SELECT j FROM JMSJobQueueBundle:Job j WHERE j.command = :command AND j.state <> :state")
            ->setParameter('command', $command)
            ->setParameter('state', Job::STATE_FINISHED)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!$currJob) {
            $job = new Job($command);

            $this->em->persist($job);
        }
    }

    /**
     * Get search index repository
     *
     * @return \Oro\Bundle\SearchBundle\Entity\Repository\SearchIndexRepository
     */
    protected function getIndexRepo()
    {
        if (!is_object($this->searchRepo)) {
            $this->searchRepo = $this->em->getRepository('OroSearchBundle:Item');
            $this->searchRepo->setDriversClasses($this->container->getParameter('oro_search.engine_orm'));
        }

        return $this->searchRepo;
    }

    /**
     * Get job repository
     *
     * @return \JMS\JobQueueBundle\Entity\Repository\JobRepository
     */
    protected function getJobRepo()
    {
        if (!is_object($this->jobRepo)) {
            $this->jobRepo = $this->em->getRepository('JMSJobQueueBundle:Job');
        }

        return $this->jobRepo;
    }

    /**
     * Get url for entity
     *
     * @param object $entity
     *
     * @return string
     */
    protected function getEntityUrl($entity)
    {
        if (isset($this->mappingConfig[get_class($entity)]['route'])) {
            $routeParameters = $this->mappingConfig[get_class($entity)]['route'];
            $routeData = array();
            if (isset($routeParameters['parameters']) && count($routeParameters['parameters'])) {
                foreach ($routeParameters['parameters'] as $parameter => $field) {
                    $routeData[$parameter] = $this->getFieldValue($entity, $field);
                }
            }

            return $this->container->get('router')->generate(
                $routeParameters['name'],
                $routeData,
                true
            );
        }

        return '';
    }

    /**
     * Get entity string
     *
     * @param object $entity
     *
     * @return string
     */
    protected function getEntityTitle($entity)
    {
        if (isset($this->mappingConfig[get_class($entity)]['title_fields'])) {
            $fields = $this->mappingConfig[get_class($entity)]['title_fields'];
            $title = array();
            foreach ($fields as $field) {
                $title[] = $this->getFieldValue($entity, $field);
            }
        } else {
            $title = array((string) $entity);
        }

        return implode(' ', $title);
    }

    /**
     * Truncate search tables
     */
    protected function clearSearchIndex()
    {
        $connection = $this->em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->beginTransaction();
        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $this->truncate($dbPlatform, $connection, 'OroSearchBundle:Item');
            $this->truncate($dbPlatform, $connection, 'OroSearchBundle:IndexDecimal');
            $this->truncate($dbPlatform, $connection, 'OroSearchBundle:IndexText');
            $this->truncate($dbPlatform, $connection, 'OroSearchBundle:IndexInteger');
            $this->truncate($dbPlatform, $connection, 'OroSearchBundle:IndexDatetime');
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
        }

    }

    /**
     * Truncate query for table
     *
     * @param $dbPlatform
     * @param $connection
     * @param $table
     */
    protected function truncate($dbPlatform, $connection, $table)
    {
        $cmd = $this->em->getClassMetadata($table);
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeUpdate($q);
    }
}
