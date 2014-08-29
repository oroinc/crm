<?php

namespace OroCRM\Bundle\MarketingListBundle\Model;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\JoinIdentifierHelper;
use Oro\Bundle\UIBundle\Tools\ArrayUtils;

class ContactInformationFieldHelper
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param AbstractQueryDesigner $queryDesigner
     *
     * @return array
     */
    public function getContactInformationColumns(AbstractQueryDesigner $queryDesigner)
    {
        $columns = array();

        // If definition is empty there is no one contact information field
        $definition = $queryDesigner->getDefinition();
        if (!$definition) {
            return $columns;
        }

        $definition = json_decode($definition, JSON_OBJECT_AS_ARRAY);
        if (empty($definition['columns'])) {
            return $columns;
        }

        $entity = $queryDesigner->getEntity();
        $identifierHelper = new JoinIdentifierHelper($entity);
        $entityContactInfoColumns = array();
        foreach ($definition['columns'] as $column) {
            $className = $identifierHelper->getEntityClassName($column['name']);
            $fieldName = $identifierHelper->getFieldName($column['name']);

            if (!array_key_exists($className, $entityContactInfoColumns)) {
                $entityContactInfoColumns[$className] = $this->getEntityLevelContactInfoColumns($className);
            }

            if (!empty($entityContactInfoColumns[$className][$fieldName])) {
                $contactInformationType = $entityContactInfoColumns[$className][$fieldName];
            } elseif ($this->configProvider->hasConfig($className, $fieldName)) {
                $fieldConfiguration = $this->configProvider->getConfig($className, $fieldName);
                $contactInformationType = $fieldConfiguration->get('contact_information');
            }

            if (!empty($contactInformationType)) {
                $columns[$contactInformationType][] = $column;
            }
        }

        return $columns;
    }

    /**
     * @param string $entity
     * @return array
     */
    protected function getEntityLevelContactInfoColumns($entity)
    {
        $contactInfoColumns = array();
        if ($this->configProvider->hasConfig($entity)) {
            $entityContactInformation = $this->configProvider
                ->getConfig($entity)
                ->get('contact_information');

            if ($entityContactInformation) {
                foreach ($entityContactInformation as $contactInfoType => $contactInfoFields) {
                    $entityColumns = ArrayUtils::arrayColumn($contactInfoFields, 'fieldName');
                    foreach ($entityColumns as $entityColumn) {
                        $contactInfoColumns[$entityColumn] = $contactInfoType;
                    }
                }
            }
        }

        return $contactInfoColumns;
    }
}
