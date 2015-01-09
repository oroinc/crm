<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Processor;

use Doctrine\Common\Collections\Collection;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface;

abstract class AbstractReverseProcessor implements ProcessorInterface
{
    const SOURCE = 0;
    const CHECKING = 1;
    const MODIFIER = 2;
    const UPDATE_ENTITY = 'update';
    const DELETE_ENTITY = 'delete';
    const NEW_ENTITY    = 'new';

    /** @var array */
    protected $checkEntityClasses = [];

    /** @var PropertyAccess */
    protected $accessor;

    /**
     * @param object $entity
     *
     * @return array
     */
    public function process($entity)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();

        $result = [
            'object' => [],
            'entity' => $entity,
        ];

        $magentoId =  $entity->getOriginId();

        foreach ($this->checkEntityClasses as $classNames => $classMapConfig) {
            if ($entity instanceof $classNames && $entity->getChannel()) {
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
                        $relations = $this->getValue($entity, $relationClassMapConfig['method']);

                        $allRelationsCheckingEntity = $this->getValue($entity, $classMapConfig['checking']);

                        if ($relations instanceof Collection) {
                            $relations = $relations->getValues();
                        }

                        if (!is_array($relations)) {
                            $relations = [$relations];
                        }

                        $checkedIdsRelations = [];
                        $result['object'][$relationName] = [];

                        foreach ($relations as $relation) {
                            $relationArray = ['object'=>[]];

                            try {
                                $this->fieldPlaceholder(
                                    $relation,
                                    $relationClassMapConfig['class'],
                                    $relationArray['object'],
                                    $relationClassMapConfig['fields']
                                );

                                if (!empty($relationArray['object'])) {
                                    $relationArray['status'] = self::UPDATE_ENTITY;
                                }

                                array_push(
                                    $checkedIdsRelations,
                                    $this->getValue($relation, $relationClassMapConfig['checking'])
                                );
                            } catch (\Exception $e) {
                                $relationArray['status'] = self::DELETE_ENTITY;
                            }

                            if (!empty($relationArray)) {
                                array_push(
                                    $result['object'][$relationName],
                                    array_merge($relationArray, ['entity' => $relation])
                                );
                            }
                        }

                        $this->addNew(
                            $allRelationsCheckingEntity,
                            $checkedIdsRelations,
                            $result['object'][$relationName],
                            $magentoId
                        );
                    }
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
                    /**
                     * @todo: will be fix, due to CRM-789
                     * reason: We need this because getBapAddressData this method work with
                     * arrays in CustomerDenormalizer
                     */
                    if (is_int($name)) {
                        $resultFieldName = $methods[self::SOURCE];
                    } else {
                        $resultFieldName = $name;
                    }

                    if (!empty($methods[self::MODIFIER])) {
                        $modifier = $methods[self::CHECKING] . '.' . $methods[self::MODIFIER];
                        $result[$resultFieldName] = $this->getValue($entity, $modifier);
                    } else {
                        try {
                            $result[$resultFieldName] = $this->getValue($entity, $methods[self::CHECKING]);
                        } catch (\Exception $e) {
                            $result[$resultFieldName] = null;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param object $entity
     * @param array $paths
     *
     * @return bool
     */
    protected function isChanged($entity, array $paths)
    {
        try {
            if (!empty($paths[self::MODIFIER])) {
                $checking = $this->getValue($entity, $paths[self::CHECKING] . '.' . $paths[self::MODIFIER]);
                $source   = $this->getValue($entity, $paths[self::SOURCE] . '.' . $paths[self::MODIFIER]);
            } else {
                $checking = $this->getValue($entity, $paths[self::CHECKING]);
                $source   = $this->getValue($entity, $paths[self::SOURCE]);
            }
        } catch (\Exception $e) {
            return true;
        }

        if (is_object($checking) && !($checking instanceof \DateTime)) {
            try {
                $checking = (string)$checking;
            } catch (\Exception $e) {
                return false;
            }
        }

        if ($checking instanceof \DateTime) {
            $checking = $checking->format('Y-m-d H:i:s');
        }

        if ($source instanceof \DateTime) {
            $source = $source->format('Y-m-d H:i:s');
        }

        return $source !== $checking;
    }

    /**
     * @param object $entities
     * @param array $checkedIds
     * @param array $result
     * @param int $magentoId
     */
    protected function addNew($entities, array $checkedIds, array &$result, $magentoId)
    {
        foreach ($entities as $entity) {
            if (!in_array($entity->getId(), $checkedIds)) {
                array_push(
                    $result,
                    ['status' => self::NEW_ENTITY, 'entity'=>$entity, 'magentoId'=>$magentoId]
                );
            }
        }
    }

    /**
     * @param object $entity
     * @param string $path
     *
     * @return mixed
     */
    protected function getValue($entity, $path)
    {
        return $this->accessor->getValue($entity, $path);
    }
}
