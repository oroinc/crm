<?php

namespace Oro\Bundle\SearchBundle\Engine;

use Doctrine\Common\Persistence\ObjectManager;

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

    public function __construct(ObjectManager $em, ContainerInterface $container, $mappingConfig)
    {
        $this->container = $container;
        $this->em = $em;
        $this->mappingConfig = $mappingConfig;
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
        if ($query->getMaxResults() > 0 || $query->getFirstResult() > 0) {
            $recordsCount = $this->getIndexRepo()->getRecordsCount($query);
        } else {
            $recordsCount = count($searchResults);
        }
        if ($searchResults) {
            foreach ($searchResults as $item) {
                /** @var $item \Oro\Bundle\SearchBundle\Entity\Item  */
                $results[] = new ResultItem($this->em, $item->getEntity(), $item->getRecordId());
            }
        }

        return array(
            'results' => $results,
            'records_count' => $recordsCount
        );
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
        $item = $this->getIndexRepo()->findOneBy(array(
            'entity'   => get_class($entity),
            'recordId' => $entity->getId()
        ));

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
            $item = $this->getIndexRepo()->findOneBy(array(
                'entity'   => $name,
                'recordId' => $entity->getId()
            ));

            if (!$item) {
                $item = new Item();

                $item->setEntity($name)
                     ->setRecordId($entity->getId());
            }

            $item->setChanged(!$realtime);

            if ($realtime) {
                $item->saveItemData($data);
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

            foreach ($config['fields'] as $field) {

                // check field relation type and set it to null if field doesn't have relations
                if (!isset($field['relation_type'])) {
                    $field['relation_type'] = 'none';
                }

                $value = $this->getFieldValue($object, $field['name']);

                switch ($field['relation_type']) {
                    case Indexer::RELATION_ONE_TO_ONE:
                    case Indexer::RELATION_MANY_TO_MANY:
                        $objectData = $this->setRelatedFields($objectData, $field['relation_fields'], $value);

                        break;
                    case Indexer::RELATION_MANY_TO_MANY:
                    case Indexer::RELATION_ONE_TO_MANY:
                        foreach ($value as $relationObject) {
                            $objectData = $this->setRelatedFields($objectData, $field['relation_fields'], $relationObject);
                        }

                        break;
                    default:
                        $objectData = $this->setDataValue($objectData, $field, $value);
                }
            }
            if (isset($config['flexible'])) {
                $objectData =  $this->setFlexibleFields($object, $objectData, $config['flexible']);
            }
        }

        return $objectData;
    }

    /**
     * Map Flexible entity fields
     *
     * @param $object
     * @param array $objectData
     * @param string $managerName
     *
     * @return array
     */
    protected function setFlexibleFields($object, $objectData, $managerName)
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
                        $value = $object->getValueData($attribute->getCode());
                        $attributeType = $attribute->getBackendType();

                        switch ($attributeType) {
                            case AbstractAttributeType::BACKEND_TYPE_TEXT:
                            case AbstractAttributeType::BACKEND_TYPE_VARCHAR:
                                $objectData = $this->saveFlexibleTextData($objectData, $attribute->getCode(), $value);
                                break;
                            case AbstractAttributeType::BACKEND_TYPE_DATETIME:
                            case AbstractAttributeType::BACKEND_TYPE_DATE:
                                $objectData = $this->saveFlexibleData(
                                    $objectData,
                                    AbstractAttributeType::BACKEND_TYPE_DATETIME,
                                    $attribute->getCode(),
                                    $value
                                );
                                break;
                            default:
                                $objectData = $this->saveFlexibleData(
                                    $objectData,
                                    $attributeType,
                                    $attribute->getCode(),
                                    $value
                                );
                        }

                    }
                }
            }
        }

        return $objectData;
    }

    /**
     * @param array $objectData
     * @param string $attributeType
     * @param string $attribute
     * @param mixed $value
     *
     * @return array
     */
    protected function saveFlexibleData($objectData, $attributeType, $attribute, $value)
    {
        if ($attributeType != AbstractAttributeType::BACKEND_TYPE_OPTION) {
            $objectData[$attributeType][$attribute] = $value;
        }

        return $objectData;
    }

    /**
     * @param array $objectData
     * @param string $attribute
     * @param mixed $value
     *
     * @return array
     */
    protected function saveFlexibleTextData($objectData, $attribute, $value)
    {
        if (!isset($objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][$attribute])) {
            $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][$attribute] = '';
        }
        $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][$attribute] .= " " . $value;
        if (!isset($objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][Indexer::TEXT_ALL_DATA_FIELD])) {
            $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][Indexer::TEXT_ALL_DATA_FIELD] = '';
        }
        $objectData[AbstractAttributeType::BACKEND_TYPE_TEXT][Indexer::TEXT_ALL_DATA_FIELD] .= " " . $value;

        return $objectData;
    }

    /**
     * Set related fields values
     *
     * @param array  $objectData
     * @param array  $relationFields
     * @param object $relationObject
     *
     * @return array
     */
    protected function setRelatedFields($objectData, $relationFields, $relationObject)
    {
        foreach ($relationFields as $relationObjectField) {
            $objectData = $this->setDataValue(
                $objectData,
                $relationObjectField,
                $this->getFieldValue($relationObject, $relationObjectField['name'])
            );
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
     * @param array $objectData
     * @param array $fieldConfig
     * @param mixed $value
     *
     * @return array
     */
    protected function setDataValue($objectData, $fieldConfig, $value)
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
            }
            $objectData[$fieldConfig['target_type']][$targetField] .= " " . $value;
            if (!isset($objectData[$fieldConfig['target_type']][Indexer::TEXT_ALL_DATA_FIELD])) {
                $objectData[$fieldConfig['target_type']][Indexer::TEXT_ALL_DATA_FIELD] = '';
            }
            $objectData[$fieldConfig['target_type']][Indexer::TEXT_ALL_DATA_FIELD] .= " " . $value;
        }

        return $objectData;
    }

    /**
     * Add reindex task to job queue if it has not been added earlier
     */
    protected function reindexJob()
    {
        // check if reindex task has not been added earlier
        $command = 'oro:search:reindex';
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
}
