<?php

namespace OroCRM\Bundle\MarketingListBundle\Provider;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Util\ClassUtils;

use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use OroCRM\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class ContactInformationFieldsProvider
{
    const CONTACT_INFORMATION_SCOPE_EMAIL = 'email';
    const CONTACT_INFORMATION_SCOPE_PHONE = 'phone';

    /**
     * @var ContactInformationFieldHelper
     */
    protected $contactInformationFieldHelper;

    /**
     * @param ContactInformationFieldHelper $contactInformationFieldHelper
     */
    public function __construct(ContactInformationFieldHelper $contactInformationFieldHelper)
    {
        $this->contactInformationFieldHelper = $contactInformationFieldHelper;
    }

    /**
     * @param AbstractQueryDesigner $queryDesigner
     * @param string|null $type
     *
     * @return array
     */
    public function getQueryTypedFields(AbstractQueryDesigner $queryDesigner, $type = null)
    {
        $entityClass = $queryDesigner->getEntity();
        $typedFields = $this->getEntityTypedFields($entityClass, $type);

        $definitionColumns = [];
        $definition = $queryDesigner->getDefinition();
        if ($definition) {
            $definition = json_decode($definition, JSON_OBJECT_AS_ARRAY);
            if (!empty($definition['columns'])) {
                $definitionColumns = array_map(
                    function (array $columnDefinition) {
                        return $columnDefinition['name'];
                    },
                    $definition['columns']
                );
            }
        }

        if (!empty($definitionColumns)) {
            $typedFields = array_intersect($typedFields, $definitionColumns);
        }

        return $typedFields;
    }

    /**
     * @param string|object $entityOrClass
     * @param string|null $type
     * @return array
     */
    public function getEntityTypedFields($entityOrClass, $type = null)
    {
        $entityOrClass = ClassUtils::getRealClass($entityOrClass);

        $contactInformationFields = $this
            ->contactInformationFieldHelper
            ->getEntityContactInformationColumns($entityOrClass);

        if (empty($contactInformationFields)) {
            return [];
        }

        if ($type) {
            $contactInformationFields = $this->filterByType($contactInformationFields, $type);
        }

        return $contactInformationFields;
    }

    /**
     * @param MarketingList $marketingList
     * @param string|null $type
     * @return array
     */
    public function getMarketingListTypedFields(MarketingList $marketingList, $type = null)
    {
        if ($marketingList->isManual()) {
            $typedFields = $this->getEntityTypedFields(
                $marketingList->getEntity(),
                $type
            );
        } else {
            $typedFields = $this->getQueryTypedFields(
                $marketingList->getSegment(),
                $type
            );
        }

        return $typedFields;
    }

    /**
     * @param array $typedFields
     * @param object|array $source
     * @return array
     */
    public function getTypedFieldsValues(array $typedFields, $source)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $fieldValues = array_map(
            function ($typedField) use ($propertyAccessor, $source) {
                if (is_array($source)) {
                    $typedField = sprintf('[%s]', $typedField);
                }

                return (string)$propertyAccessor->getValue($source, $typedField);
            },
            $typedFields
        );

        return array_unique($fieldValues);
    }

    /**
     * @param array $contactInformationFields
     * @param string $type
     * @return array
     */
    protected function filterByType(array $contactInformationFields, $type)
    {
        return array_keys(
            array_filter(
                $contactInformationFields,
                function ($contactInformationField) use ($type) {
                    return $contactInformationField === $type;
                }
            )
        );
    }
}
