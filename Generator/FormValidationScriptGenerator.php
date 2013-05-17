<?php

namespace Oro\Bundle\JsFormValidationBundle\Generator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Mapping\ClassMetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

use APY\JsFormValidationBundle\Generator\FormValidationScriptGenerator as BaseFormValidationScriptGenerator;

class FormValidationScriptGenerator extends BaseFormValidationScriptGenerator
{
    /**
     * @var ClassMetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @param ClassMetadata[] $classesMetadata
     */
    protected $classesMetadata;

    /**
     * @param ContainerInterface $container
     * @param ClassMetadataFactoryInterface $metadataFactory
     */
    public function __construct(ContainerInterface $container, ClassMetadataFactoryInterface $metadataFactory)
    {
        parent::__construct($container);
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * Gets ClassMetadata of desired class with annotations and others (xml, yml, php) using metadata factory
     *
     * @param string $className
     * @return ClassMetadata Returns ClassMetadata object of desired entity with annotations info
     */
    public function getClassMetadata($className)
    {
        if (!isset($this->classesMetadata[$className])) {
            $this->classesMetadata[$className] = $this->metadataFactory->getClassMetadata($className);
        }

        return $this->classesMetadata[$className];
    }
}
