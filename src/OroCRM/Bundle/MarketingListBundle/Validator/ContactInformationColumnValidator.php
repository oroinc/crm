<?php

namespace OroCRM\Bundle\MarketingListBundle\Validator;

use Oro\Bundle\QueryDesignerBundle\QueryDesigner\JoinIdentifierHelper;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ContactInformationColumnValidator extends ConstraintValidator
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
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($constraint->field && !is_string($constraint->field)) {
            throw new UnexpectedTypeException($constraint->field, 'string');
        }

        if (!empty($constraint->field)) {
            $propertyAccess = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccess->getValue($value, $constraint->field);
        }

        if ($value instanceof AbstractQueryDesigner && !$this->assertContactInformationFields($value)) {
            if ($constraint->field) {
                $this->context->addViolationAt($constraint->field, $constraint->message);
            } else {
                $this->context->addViolation($constraint->message);
            }
        }
    }

    /**
     * Assert that value has contact information column in it's definition.
     *
     * @param AbstractQueryDesigner $value
     * @return bool
     */
    protected function assertContactInformationFields(AbstractQueryDesigner $value)
    {
        $entity = $value->getEntity();
        // If entity has no configuration it has no contact information fields
        if (!$this->configProvider->hasConfig($entity)) {
            return false;
        }

        // If definition is empty there is no one contact information field
        $definition = $value->getDefinition();
        if (!$definition) {
            return false;
        }

        $definition = json_decode($definition, JSON_OBJECT_AS_ARRAY);
        if (empty($definition['columns'])) {
            return false;
        }

        $identifierHelper = new JoinIdentifierHelper($entity);
        foreach ($definition['columns'] as $column) {
            $className = $identifierHelper->getEntityClassName($column['name']);
            $fieldName = $identifierHelper->getFieldName($column['name']);
            if ($this->configProvider->hasConfig($className, $fieldName)) {
                $fieldConfiguration = $this->configProvider->getConfig($className, $fieldName);
                $contactInformationType = $fieldConfiguration->get('contact_information');
                if (!empty($contactInformationType)) {
                    return true;
                }
            }
        }

        return false;
    }
}
