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
     * @param AbstractQueryDesigner $abstractQueryDesigner
     * @param string $entityClass
     * @param string $type
     *
     * @return array
     */
    public function getQueryTypedFields(AbstractQueryDesigner $abstractQueryDesigner, $entityClass, $type)
    {
        $typedFields = $this->getEntityTypedFields($entityClass, $type);

        $definitionColumns = [];
        $definition = $abstractQueryDesigner->getDefinition();
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
     * @param string $type
     * @return array
     */
    public function getEntityTypedFields($entityOrClass, $type)
    {
        $entityOrClass = ClassUtils::getRealClass($entityOrClass);

        $contactInformationFields = $this
            ->contactInformationFieldHelper
            ->getEntityContactInformationColumns($entityOrClass);

        if (empty($contactInformationFields)) {
            return [];
        }

        return array_keys(
            array_filter(
                $contactInformationFields,
                function ($contactInformationField) use ($type) {
                    return $contactInformationField === $type;
                }
            )
        );
    }

    /**
     * @param MarketingList $marketingList
     * @param string $type
     * @return array
     */
    public function getMarketingListTypedFields(MarketingList $marketingList, $type)
    {
        if ($marketingList->isManual()) {
            $typedFields = $this->getEntityTypedFields(
                $marketingList->getEntity(),
                $type
            );
        } else {
            $typedFields = $this->getQueryTypedFields(
                $marketingList->getSegment(),
                $marketingList->getEntity(),
                $type
            );
        }

        return $typedFields;
    }

    /**
     * @param array $typedFields
     * @param object $entity
     * @return array
     */
    public function getTypedFieldsValues(array $typedFields, $entity)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return array_map(
            function ($typedField) use ($propertyAccessor, $entity) {
                return (string)$propertyAccessor->getValue($entity, $typedField);
            },
            $typedFields
        );
    }
}
