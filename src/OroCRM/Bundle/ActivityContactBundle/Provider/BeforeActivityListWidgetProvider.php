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

class BeforeActivityListWidgetProvider implements WidgetProviderInterface
{
    /** @var ConfigProviderInterface */
    protected $activityConfigProvider;

    /** @var ConfigProviderInterface */
    protected $entityProvider;

    /** @var ConfigProviderInterface */
    protected $extendProvider;

    /** @var PropertyAccessor */
    protected $propertyAccessor;

    protected static $contactFields = [
        'ac_last_contact_date',
        'ac_last_contact_date_out',
        'ac_last_contact_date_in',
        'ac_contact_count',
        'ac_contact_count_out',
        'ac_contact_count_in',
        'last_in',
        'count_in',
        'contact_percenrt'
    ];

    public function __construct(ConfigManager $configManager)
    {
        $this->activityConfigProvider = $configManager->getProvider('activity');
        $this->entityProvider   = $configManager->getProvider('entity');
        $this->extendProvider = $configManager->getProvider('extend');
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        //return true;
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
            $fieldConfigId = $field->getId();
            $fieldConfig = $this->entityProvider->getConfigById($fieldConfigId);
            $fieldName = $fieldConfigId->getFieldName();
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
        return $this->extendProvider->filter(
            function (ConfigInterface $config) {
                $extendConfig = $this->extendProvider->getConfigById($config->getId());
                /** @var FieldConfigId $fieldConfigId */
                $fieldConfigId = $extendConfig->getId();
                return in_array($fieldConfigId->getFieldName(), self::$contactFields);
            },
            ClassUtils::getClass($entity)
        );
    }
}
