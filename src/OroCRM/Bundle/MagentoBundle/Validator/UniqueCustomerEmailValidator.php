<?php

namespace OroCRM\Bundle\MagentoBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use OroCRM\Bundle\MagentoBundle\Validator\Constraints\UniqueCustomerEmailConstraint;

class UniqueCustomerEmailValidator extends ConstraintValidator
{
    /**
     * @var TransportInterface|MagentoTransportInterface
     */
    protected $transport;

    /**
     * @param TransportInterface $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param Customer $value
     * @param UniqueCustomerEmailConstraint|Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof Customer && !$value->getOriginId()) {
            $this->transport->init($value->getChannel()->getTransport());

            // TODO Filter customers by email, store and website. Remove current user
            $customers = $this->transport->getCustomers($value->getEmail());
            if (count($customers) > 0) {
                $this->context->addViolationAt('name', $constraint->message);
            }
        }
    }
}
