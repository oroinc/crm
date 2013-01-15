<?php

namespace Oro\Bundle\SearchBundle\Engine;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Util\PropertyPath;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result\Item as ResultItem;
use Oro\Bundle\SearchBundle\Entity\Item;

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
        if ($searchResults) {
            foreach ($searchResults as $item) {
                /** @var $item \Oro\Bundle\SearchBundle\Entity\Item  */
                $results[] = new ResultItem($this->em, $item->getEntity(), $item->getId());
            }
        }

        return $results;
    }

    /**
     * Delete record from index
     *
     * @param string $objectName
     * @param int    $id
     *
     * @return bool|int
     */
    public function delete($objectName, $id)
    {
        $index = $this->getIndexRepo()->findOneBy(
            array(
                 'entity'   => $objectName,
                 'recordId' => $id
            )
        );
        if ($index) {
            $recordId = $index->getId();
            $this->em->remove($index);
            $this->em->flush();

            return $recordId;
        }

        return false;
    }

    /**
     * Insert or update record
     *
     * @param string $objectName
     * @param object $object
     *
     * @return bool|int
     */
    public function save($objectName, $object)
    {
        $objectData = $this->mapObject($object);
        if (count($objectData)) {
            $item = $this->getIndexRepo()->findOneBy(
                array(
                     'entity'   => $objectName,
                     'recordId' => $object->getId()
                )
            );
            if (!$item) {
                $item = new Item();
                $item->setEntity($objectName);
                $item->setRecordId($object->getId());
            }

            $item->saveItemData($objectData);
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
                    case 'one-to-one':
                    case 'many-to-one':
                        $objectData = $this->setRelatedFields($objectData, $field['relation_fields'], $value);
                        break;
                    case 'many-to-many':
                    case 'one-to-many':
                        foreach ($value as $relationObject) {
                            $objectData = $this->setRelatedFields($objectData, $field['relation_fields'], $relationObject);
                        }
                        break;
                    default:
                        $objectData = $this->setDataValue($objectData, $field, $value);
                }
            }
        }

        return $objectData;
    }

    /**
     * Set related fields values
     *
     * @param array $objectData
     * @param array $relationFields
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
     * @param string $fieldName
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
                $objectData[$fieldConfig['target_type']][$targetField] .= " " . $value;
            }
        }

        return $objectData;
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
}