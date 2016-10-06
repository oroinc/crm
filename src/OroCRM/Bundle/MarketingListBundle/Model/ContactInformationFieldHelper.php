<?php

namespace OroCRM\Bundle\MarketingListBundle\Model;

use Oro\Component\PhpUtils\ArrayUtil;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\JoinIdentifierHelper;

class ContactInformationFieldHelper
{
    /** @var ConfigProvider */
    protected $configProvider;

    /** @var array */
    protected $entityContactInfoColumns = array();

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var EntityFieldProvider */
    protected $fieldProvider;

    /**
     * @param ConfigProvider $configProvider
     * @param DoctrineHelper $doctrineHelper
     * @param EntityFieldProvider $fieldProvider
     */
    public function __construct(
        ConfigProvider $configProvider,
        DoctrineHelper $doctrineHelper,
        EntityFieldProvider $fieldProvider
    ) {
        $this->configProvider = $configProvider;
        $this->doctrineHelper = $doctrineHelper;
        $this->fieldProvider = $fieldProvider;
    }

    /**
     * @param AbstractQueryDesigner $queryDesigner
     *
     * @return array
     */
    public function getQueryContactInformationFields(AbstractQueryDesigner $queryDesigner)
    {
        $fields = [];

        // If definition is empty there is no one contact information field
        $definition = $queryDesigner->getDefinition();
        if (!$definition) {
            return $fields;
        }

        $definition = json_decode($definition, JSON_OBJECT_AS_ARRAY);
        if (empty($definition['columns'])) {
            return $fields;
        }

        $entity = $queryDesigner->getEntity();
        foreach ($definition['columns'] as $column) {
            $contactInformationType = $this->getContactInformationFieldType($entity, $column['name']);
            if (!empty($contactInformationType)) {
                $fields[$contactInformationType][] = $column;
            }
        }

        return $fields;
    }

    /**
     * Get entity contact information fields.
     *
     * @param string|object $entity
     * @return array
     */
    public function getEntityContactInformationFields($entity)
    {
        $metadata = $this->doctrineHelper->getEntityMetadata($entity);
        $fields = $metadata->getFieldNames();
        $contactInformationFields = [];
        foreach ($fields as $field) {
            if ($type = $this->getContactInformationFieldType($entity, $field)) {
                $contactInformationFields[$field] = $type;
            }
        }

        return array_merge($contactInformationFields, $this->getEntityLevelContactInfoFields($entity));
    }

    /**
     * @param string $entity
     * @return array
     */
    public function getEntityContactInformationFieldsInfo($entity)
    {
        $fields = [];
        $contactInformationFields = $this->getEntityContactInformationFields($entity);
        $entityFields = $this->fieldProvider->getFields($entity, false, true);

        foreach ($entityFields as $entityField) {
            if (array_key_exists($entityField['name'], $contactInformationFields)) {
                $entityField['contact_information_type'] = $contactInformationFields[$entityField['name']];
                $fields[] = $entityField;
            }
        }

        return $fields;
    }

    /**
     * @param string $entity
     * @param string $fieldName
     * @return string|null
     */
    public function getContactInformationFieldType($entity, $fieldName)
    {
        $contactInformationType = null;
        $identifierHelper = new JoinIdentifierHelper($entity);
        $className = $identifierHelper->getEntityClassName($fieldName);
        $fieldName = $identifierHelper->getFieldName($fieldName);

        if (!array_key_exists($className, $this->entityContactInfoColumns)) {
            $this->entityContactInfoColumns[$className] = $this->getEntityLevelContactInfoFields($className);
        }

        if (!empty($this->entityContactInfoColumns[$className][$fieldName])) {
            $contactInformationType = $this->entityContactInfoColumns[$className][$fieldName];
        } elseif ($this->configProvider->hasConfig($className, $fieldName)) {
            $fieldConfiguration = $this->configProvider->getConfig($className, $fieldName);
            $contactInformationType = $fieldConfiguration->get('contact_information');
        }

        return $contactInformationType;
    }

    /**
     * @param string $entity
     * @return array
     */
    protected function getEntityLevelContactInfoFields($entity)
    {
        $result = [];
        if ($this->configProvider->hasConfig($entity)) {
            $entityContactInformation = $this->configProvider
                ->getConfig($entity)
                ->get('contact_information');

            if ($entityContactInformation) {
                foreach ($entityContactInformation as $contactInfoType => $contactInfoFields) {
                    $entityColumns = ArrayUtil::arrayColumn($contactInfoFields, 'fieldName');
                    foreach ($entityColumns as $entityColumn) {
                        $result[$entityColumn] = $contactInfoType;
                    }
                }
            }
        }

        return $result;
    }
}
