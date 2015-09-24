<?php

namespace OroCRM\Bundle\MagentoBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\BatchFilterBag;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use OroCRM\Bundle\MagentoBundle\Validator\Constraints\UniqueCustomerEmailConstraint;

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
            $customers = $this->getRemoteCustomers($value);

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
     * @return array
     */
    protected function getRemoteCustomers($value)
    {
        $this->transport->init($value->getChannel()->getTransport());

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
        $customers = $this->transport->call(SoapTransport::ACTION_CUSTOMER_LIST, $filters);

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
