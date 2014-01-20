<?php

namespace OroCRM\Bundle\SalesBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ValidatorException;

use Doctrine\Common\Persistence\ManagerRegistry;

use OroCRM\Bundle\SalesBundle\Entity\Repository\SalesFlowOpportunityRepository;
use OroCRM\Bundle\SalesBundle\Entity\SalesFlowOpportunity;

class OpportunityWithSalesFlowValidator extends ConstraintValidator
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }



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

        /** @var SalesFlowOpportunityRepository $repository */
        $repository = $this->registry->getRepository('OroCRMSalesBundle:SalesFlowOpportunity');
        $salesFlowOpportunity = $repository->findOneByOpportunity($opportunity);

        if (!$salesFlowOpportunity) {
            return;
        }

        if ($salesFlowOpportunity->getId() != $value->getId()) {
            /** @var OpportunityWithSalesFlow $constraint */
            $this->context->addViolation($constraint->message);
        }
    }
}
