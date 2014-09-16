<?php

namespace OroCRM\Bundle\MarketingListBundle\Provider;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Util\ClassUtils;

use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use OroCRM\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;

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
     * @param object $entity
     * @param string $type
     *
     * @return string[]
     */
    public function getQueryContactInformationFields(AbstractQueryDesigner $abstractQueryDesigner, $entity, $type)
    {
        $contactInformationFields = $this
            ->contactInformationFieldHelper
            ->getEntityContactInformationColumns(ClassUtils::getRealClass($entity));

        if (empty($contactInformationFields)) {
            return [];
        }

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

        $typedFields = array_keys(
            array_filter(
                $contactInformationFields,
                function ($contactInformationField) use ($type) {
                    return $contactInformationField === $type;
                }
            )
        );

        if (!empty($definitionColumns)) {
            $typedFields = array_intersect($typedFields, $definitionColumns);
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return array_map(
            function ($typedField) use ($propertyAccessor, $entity) {
                return (string)$propertyAccessor->getValue($entity, $typedField);
            },
            $typedFields
        );
    }
}
