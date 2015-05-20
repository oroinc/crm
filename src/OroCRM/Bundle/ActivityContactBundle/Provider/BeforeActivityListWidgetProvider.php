<?php

namespace OroCRM\Bundle\ActivityContactBundle\Provider;

use Doctrine\Common\Util\ClassUtils;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProviderInterface;
use Oro\Bundle\UIBundle\Provider\WidgetProviderInterface;

use OroCRM\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;

class BeforeActivityListWidgetProvider implements WidgetProviderInterface
{
    /** @var ConfigProviderInterface */
    protected $entityProvider;

    /** @var ConfigProviderInterface */
    protected $extendProvider;

    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->entityProvider         = $configManager->getProvider('entity');
        $this->extendProvider         = $configManager->getProvider('extend');
        $this->propertyAccessor       = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return (bool)$this->getEntityActivityContactFields($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgets($object)
    {
        $result = [];
        foreach ($this->getEntityActivityContactFields($object) as $field) {
            /** @var FieldConfigId $fieldConfigId */
            $fieldConfigId      = $field->getId();
            $fieldConfig        = $this->entityProvider->getConfigById($fieldConfigId);
            $fieldName          = $fieldConfigId->getFieldName();
            $result[$fieldName] = [
                'type'  => $fieldConfigId->getFieldType(),
                'label' => $fieldConfig->get('label'),
                'value' => $this->propertyAccessor->getValue($object, $fieldName),
            ];
        }

        return $result;
    }

    /**
     * @param $entity
     * @return array|ConfigInterface[]
     */
    protected function getEntityActivityContactFields($entity)
    {
        $fields = array_keys(ActivityScope::$fieldsConfiguration);

        return $this->extendProvider->filter(
            function (ConfigInterface $config) use ($fields) {
                $extendConfig = $this->extendProvider->getConfigById($config->getId());
                /** @var FieldConfigId $fieldConfigId */
                $fieldConfigId = $extendConfig->getId();

                return in_array($fieldConfigId->getFieldName(), $fields);
            },
            ClassUtils::getClass($entity)
        );
    }
}
