<?php

namespace Oro\Bundle\MagentoBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\BatchFilterBag;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\MagentoBundle\Validator\Constraints\UniqueCustomerEmailConstraint;

class UniqueCustomerEmailValidator extends ConstraintValidator
{
    /**
     * @var MagentoTransportInterface
     */
    protected $transport;

    /**
     * @param MagentoTransportInterface $transport
     */
    public function __construct(MagentoTransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param Customer $value
     * @param UniqueCustomerEmailConstraint|Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof Customer) {
            $customers = $this->getRemoteCustomers($value, $constraint);

            $customers = array_filter(
                $customers,
                function ($customerData) use ($value) {
                    if (is_object($customerData)) {
                        $customerData = (array)$customerData;
                    }
                    if ($customerData
                        && !empty($customerData['customer_id'])
                        && $customerData['customer_id'] == $value->getOriginId()
                    ) {
                        return false;
                    }

                    return true;
                }
            );

            if (count($customers) > 0) {
                $this->context->addViolationAt('email', $constraint->message);
            }
        }
    }

    /**
     * @param Customer $value
     * @param UniqueCustomerEmailConstraint|Constraint $constraint
     * @return array
     */
    protected function getRemoteCustomers($value, Constraint $constraint)
    {
        try {
            $this->transport->init($value->getChannel()->getTransport());
        } catch (\RuntimeException $e) {
            $this->context->addViolationAt('email', $constraint->transportMessage);

            return [];
        }

        $filter = new BatchFilterBag();
        $filter->addComplexFilter(
            'email',
            [
                'key' => 'email',
                'value' => [
                    'key' => 'eq',
                    'value' => $value->getEmail()
                ]
            ]
        );
        $filter->addComplexFilter(
            'store_id',
            [
                'key' => 'store_id',
                'value' => [
                    'key' => 'eq',
                    'value' => $value->getStore()->getOriginId()
                ]
            ]
        );

        $filters = $filter->getAppliedFilters();

        try {
            $customers = $this->transport->call(SoapTransport::ACTION_CUSTOMER_LIST, $filters);
        } catch (\RuntimeException $e) {
            $this->context->addViolationAt('email', $constraint->transportMessage);

            return [];
        }

        if (is_array($customers)) {
            return $customers;
        } else {
            $customers = (array) $customers;
            if (empty($customers)) {
                return [];
            } else {
                return [$customers];
            }
        }
    }
}
