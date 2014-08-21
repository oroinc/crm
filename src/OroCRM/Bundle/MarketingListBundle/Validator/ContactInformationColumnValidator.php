<?php

namespace OroCRM\Bundle\MarketingListBundle\Validator;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use OroCRM\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;

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

        if (!$this->assertContactInformationFields($value)) {
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
        return count($this->contactInformationFieldHelper->getContactInformationColumns($value)) > 0;
    }
}
