<?php

namespace OroCRM\Bundle\SalesBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ValidatorException;

use OroCRM\Bundle\SalesBundle\Entity\SalesFlowOpportunity;

class OpportunityWithSalesFlowValidator extends ConstraintValidator
{
    /**
     * @param SalesFlowOpportunity|null $value
     * @param Constraint $constraint
     * @throws ValidatorException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        if (!$value instanceof SalesFlowOpportunity) {
            throw new ValidatorException('Value must be instance of SalesFlowOpportunity');
        }

        $opportunity = $value->getOpportunity();
        if (!$opportunity) {
            return;
        }

        $salesFlowOpportunity = $opportunity->getSalesFlowOpportunity();
        if (!$salesFlowOpportunity) {
            return;
        }

        if ($salesFlowOpportunity->getId() != $value->getId()) {
            /** @var OpportunityWithSalesFlow $constraint */
            $this->context->addViolation($constraint->message);
        }
    }
}
