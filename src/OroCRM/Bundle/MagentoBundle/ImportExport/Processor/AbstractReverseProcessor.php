<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Processor;

use \Doctrine\Common\Collections\Collection;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;

abstract class AbstractReverseProcessor implements ProcessorInterface
{
    const SOURCE = 0;
    const CHECKING = 1;
    const UPDATE_ENTITY = 'update';
    const DELETE_ENTITY = 'delete';
    const NEW_ENTITY    = 'new';

    /** @var array */
    protected $checkEntityClasses = [];

    /** @var PropertyAccess */
    protected $accessor;

    public function initPropertyAccess()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param object $entity
     *
     * @return array
     */
    public function process($entity)
    {
        $this->initPropertyAccess();

        $result = [
            'object' => [],
            'entity' => $entity,
        ];


        if ($entity->getChannel() && $entity->getOriginId()) {

            foreach ($this->checkEntityClasses as $classNames => $classMapConfig) {
                try {
                    $this->fieldPlaceholder(
                        $entity,
                        $classNames,
                        $result['object'],
                        $classMapConfig['fields']
                    );
                } catch (\Exception $e) {
                    $result['status'] = self::DELETE_ENTITY;
                    return $result;
                }

                if (!empty($classMapConfig['relation'])) {

                    foreach ($classMapConfig['relation'] as $relationName => $relationClassMapConfig) {

                        $relations = $this->accessor->getValue($entity, $relationClassMapConfig['method']);

                        $allRelationsCheckingEntity = $this->accessor
                            ->getValue($entity, $classMapConfig['checking']);

                        if ($relations instanceof Collection) {
                            $relations = $relations->getValues();
                        }

                        if (!is_array($relations)) {
                            $relations = [$relations];
                        }

                        $checkedIdsRelations = [];
                        $result['object'][$relationName] = [];

                        foreach ($relations as $relation) {
                            $relationArray = [];

                            try {
                                $this->fieldPlaceholder(
                                    $relation,
                                    $relationClassMapConfig['class'],
                                    $relationArray,
                                    $relationClassMapConfig['fields']
                                );

                                $relationArray['status'] = self::UPDATE_ENTITY;

                                array_push(
                                    $checkedIdsRelations,
                                    $this->accessor->getValue($relation, $relationClassMapConfig['checking'])
                                );

                            } catch (\Exception $e) {
                                $relationArray['status'] = self::DELETE_ENTITY;
                            }
                            array_push(
                                $result['object'][$relationName],
                                array_merge($relationArray, ['entity' => $relation])
                            );
                            unset($relationArray);
                        }
                        unset($relation);

                        $this->addNew(
                            $allRelationsCheckingEntity,
                            $checkedIdsRelations,
                            $result['object'][$relationName]
                        );
                    }
                    unset($relationClassMapConfig, $relationName);
                }
            }
        }

        return (object)$result;
    }

    /**
     * @param object $entity
     * @param string $classNames
     * @param array  $result
     * @param array  $fields
     */
    protected function fieldPlaceholder(
        $entity,
        $classNames,
        array &$result,
        array $fields
    ) {
        if ($entity instanceof $classNames) {
            foreach ($fields as $name => $methods) {
                if ($this->isChanged($entity, $methods)) {
                    $result[$methods[self::SOURCE]] = $this->accessor->getValue($entity, $methods[self::CHECKING]);
                }
            }
        }
    }

    protected function isChanged($entity, array $paths)
    {
        $checking = $this->accessor->getValue($entity, $paths[self::CHECKING]);

        if (is_object($checking)) {
            try {
                $checking = (string)$checking;
            } catch (\Exception $e) {
                return false;
            }
        }

        return (
            $this->accessor->getValue($entity, $paths[self::SOURCE])
            !== $checking
        );
    }

    protected function addNew($entities, $checkedIds, &$result)
    {
        foreach ($entities as $entity) {
            if (!in_array($entity->getId(), $checkedIds)) {
                array_push(
                    $result,
                    ['status' => self::NEW_ENTITY, 'entity'=>$entity]
                );
            }
        }
    }


    /*protected function getCheckingMethodValue($entity, $checkingMethod, $methods)
    {
        if (!empty($methods[1])) {
            return $entity->$checkingMethod()->$methods[1]();
        }

        return $entity->$checkingMethod()->$methods[0]();
    }*/


    /*protected function getObjectMethodValue($entity, $methods)
    {
        return $entity->$methods[0]();
    }*/

    protected function hasDistinction($entity, $paths)
    {
        return (
            $this->accessor->getValue($entity, $paths[self::SOURCE])
            !== $this->accessor->getValue($entity, $paths[self::CHECKING])
        );

        /*
        if (!empty($methods[1])) {
            return (
                $this->getObjectMethodValue($entity, $methods)
                !== $this->getCheckingMethodValue($entity, $checkingMethod, $methods)
            );
        }

        return (
            $this->getObjectMethodValue($entity, $methods)
            !== $this->getCheckingMethodValue($entity, $checkingMethod, $methods)
        );*/
    }
}
