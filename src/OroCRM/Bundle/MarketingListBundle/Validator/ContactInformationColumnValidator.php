<?php

namespace OroCRM\Bundle\MarketingListBundle\Validator;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use OroCRM\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use OroCRM\Bundle\MarketingListBundle\Validator\Constraints\ContactInformationColumnConstraint;

class ContactInformationColumnValidator extends ConstraintValidator
{
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
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var ContactInformationColumnConstraint $constraint */
        if ($constraint->field && !is_string($constraint->field)) {
            throw new UnexpectedTypeException($constraint->field, 'string');
        }

        if (!empty($constraint->field)) {
            $propertyAccess = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccess->getValue($value, $constraint->field);
        }

        if (!$value instanceof AbstractQueryDesigner) {
            throw new UnexpectedTypeException($value, 'AbstractQueryDesigner');
        }

        $type = $constraint->type;
        if (!$this->assertContactInformationFields($value, $type)) {
            $parameters = [];
            if ($constraint->type) {
                $message = $constraint->typeMessage;
                $parameters['%type%'] = $constraint->type;
            } else {
                $message = $constraint->message;
            }

            if ($constraint->field) {
                $this->context->addViolationAt($constraint->field, $message, $parameters);
            } else {
                $this->context->addViolation($message, $parameters);
            }
        }
    }

    /**
     * Assert that value has contact information column in it's definition.
     *
     * @param AbstractQueryDesigner $value
     * @param string $type
     * @return bool
     */
    protected function assertContactInformationFields(AbstractQueryDesigner $value, $type)
    {
        $contactInformationFields = $this->contactInformationFieldHelper->getQueryContactInformationColumns($value);
        if ($type) {
            return array_key_exists($type, $contactInformationFields) && (bool)count($contactInformationFields[$type]);
        }

        return (bool)count($contactInformationFields);
    }
}
