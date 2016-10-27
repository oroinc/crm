<?php

namespace Oro\Bundle\SalesBundle\Manager;

use Doctrine\Common\Util\ClassUtils;

use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityCustomerManager
{
    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /** @var ConfigProvider */
    protected $salesConfigProvider;

    /**
     * @param PropertyAccessor $propertyAccessor
     * @param ConfigProvider $salesConfigProvider
     */
    public function __construct(PropertyAccessor $propertyAccessor, ConfigProvider $salesConfigProvider)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->salesConfigProvider = $salesConfigProvider;
    }

    /**
     * @param Opportunity $opportunity
     * @param object $customer
     *
     * @return bool
     */
    public function hasCustomer(Opportunity $opportunity, $customer)
    {
        return $customer === $this->propertyAccessor->getValue(
            $opportunity,
            $this->getCustomerProperty($customer)
        );
    }

    /**
     * @param Opportunity $opportunity
     * @param object|null $customer
     */
    public function setCustomer(Opportunity $opportunity, $customer)
    {
        $this->unsetCustomer($opportunity);

        if ($customer !== null) {
            $this->propertyAccessor->setValue(
                $opportunity,
                $this->getCustomerProperty($customer),
                $customer
            );
        }
    }

    /**
     * @param Opportunity $opportunity
     *
     * @return object|null
     */
    public function getCustomer(Opportunity $opportunity)
    {
        $customerProperties = $this->getCustomerProperties();
        foreach ($customerProperties as $customerProperty) {
            $customer = $this->propertyAccessor->getValue($opportunity, $customerProperty);
            if ($customer) {
                return $customer;
            }
        }

        return null;
    }

    /**
     * @param Opportunity $opportunity
     */
    protected function unsetCustomer(Opportunity $opportunity)
    {
        $customerProperties = $this->getCustomerProperties();
        foreach ($customerProperties as $customerProperty) {
            $this->propertyAccessor->setValue($opportunity, $customerProperty, null);
        }
    }

    /**
     * @param object $customer
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getCustomerProperty($customer)
    {
        $customerProperties = $this->getCustomerProperties();
        $customerClass = ClassUtils::getClass($customer);

        if (!isset($customerProperties[$customerClass])) {
            throw new \InvalidArgumentException(sprintf(
                'Customer is expected to be instance of one of the "%s", but "%s" given',
                implode(', ', array_keys($customerProperties)),
                $customerClass
            ));
        }

        return $customerProperties[$customerClass];
    }

    /**
     * @return array
     */
    protected function getCustomerProperties()
    {
        return $this->salesConfigProvider->getConfig(Opportunity::class)->get('customers');
    }
}
