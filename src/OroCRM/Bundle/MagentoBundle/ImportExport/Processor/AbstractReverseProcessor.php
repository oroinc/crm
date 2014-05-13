<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Processor;

use \Doctrine\Common\Collections\Collection;

use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;

abstract class AbstractReverseProcessor implements ProcessorInterface, ContextAwareInterface
{
    /** @var ContextInterface */
    protected $context;

    /** @var array */
    protected $checkEntityClasses = [];

    /**
     * @param object $entity
     *
     * @return array
     */
    public function process($entity)
    {
        $result = [
            'object' => []
        ];

        if ($entity->getChannel()) {

            foreach ($this->checkEntityClasses as $classNames => $classMapConfig) {
                $this->fieldPlaceholder(
                    $entity,
                    $classNames,
                    $result,
                    $classMapConfig['fields'],
                    $classMapConfig['checking'],
                    'object'
                );

                if (!empty($classMapConfig['relation'])) {

                    foreach ($classMapConfig['relation'] as $relationName => $relationClassMapConfig) {

                        $relations = $entity->$relationClassMapConfig['method']();

                        if ($relations instanceof Collection) {
                            $relations = $relations->getValues();
                        }

                        if (is_array($relations)) {

                            foreach ($relations as $relation) {
                                $this->fieldPlaceholder(
                                    $relation,
                                    $relationClassMapConfig['class'],
                                    $result['object'],
                                    $relationClassMapConfig['fields'],
                                    $relationClassMapConfig['checking'],
                                    $relationName
                                );
                            }
                        } else {
                            $this->fieldPlaceholder(
                                $relations,
                                $relationClassMapConfig['class'],
                                $result['object'],
                                $relationClassMapConfig['fields'],
                                $relationClassMapConfig['checking'],
                                $relationName
                            );
                        }
                    }

                }


            }

            if (!empty($result['object'])) {
                $result['channel'] = $entity->getChannel();
            }
        }

        return (object)$result;
    }

    /**
     * @param ContextInterface $context
     * @throws InvalidConfigurationException
     */
    public function setImportExportContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @param object $entity
     * @param string $classNames
     * @param array  $result
     * @param array  $fields
     * @param array  $checking
     * @param string $arrayName
     */
    protected function fieldPlaceholder(
        $entity,
        $classNames,
        array &$result,
        array $fields,
        array $checking,
        $arrayName
    ) {
        if ($entity instanceof $classNames) {
            foreach ($fields as $name => $methods) {
                if ($this->isChanged($entity, $methods, $checking)) {
                    $result[$arrayName][$name] = $this->getCheckingMethodValue($entity, $checking['method'], $methods);
                }
            }
        }
    }

    /**
     * @param object $entity
     * @param array $methods
     * @param array $checking
     *
     * @return bool
     */
    protected function isChanged($entity, array $methods, array $checking)
    {
        if (is_array($checking)) {
            $checkingMethod = $checking['method'];
            $checkingClass = $checking['class'];

            if ($entity->$checkingMethod() instanceof $checkingClass) {
                return $this->hasDistinction($entity, $checkingMethod, $methods);
            }
        }

        return false;
    }

    /**
     * @param object $entity
     * @param string $checkingMethod
     * @param string $methods
     *
     * @return mixed
     */
    protected function getCheckingMethodValue($entity, $checkingMethod, $methods)
    {
        if (!empty($methods[1])) {
            return $entity->$checkingMethod()->$methods[1]();
        }

        return $entity->$checkingMethod()->$methods[0]();
    }

    /**
     * @param object $entity
     * @param string $methods
     *
     * @return mixed
     */
    protected function getObjectMethodValue($entity, $methods)
    {
        return $entity->$methods[0]();
    }

    /**
     *
     * @param object $entity
     * @param string $checkingMethod
     * @param string $methods
     *
     * @return bool
     */
    protected function hasDistinction($entity, $checkingMethod, $methods)
    {
        if (!empty($methods[1])) {
            return (
                $this->getObjectMethodValue($entity, $methods)
                !== $this->getCheckingMethodValue($entity, $checkingMethod, $methods)
            );
        }

        return (
            $this->getObjectMethodValue($entity, $methods)
            !== $this->getCheckingMethodValue($entity, $checkingMethod, $methods)
        );
    }
}
