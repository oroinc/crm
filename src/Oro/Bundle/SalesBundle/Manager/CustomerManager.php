<?php

namespace Oro\Bundle\SalesBundle\Manager;

use Doctrine\Common\Util\ClassUtils;

use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class CustomerManager
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
     * This method should always return false as only one customer is allowed for $ownerEntity
     *
     * @param object $ownerEntity
     *
     * @return bool
     */
    public function hasMoreCustomers($ownerEntity)
    {
        $customerProperties = $this->getCustomerProperties(ClassUtils::getClass($ownerEntity));
        $customers = 0;
        foreach ($customerProperties as $customerProperty) {
            $customer = $this->propertyAccessor->getValue($ownerEntity, $customerProperty);
            if ($customer) {
                $customers++;
            }
        }

        return $customers > 1;
    }

    /**
     * @param object $ownerEntity
     * @param object $customer
     *
     * @return bool
     */
    public function hasCustomer($ownerEntity, $customer)
    {
        return $customer === $this->propertyAccessor->getValue(
            $ownerEntity,
            $this->getCustomerProperty(ClassUtils::getClass($ownerEntity), $customer)
        );
    }

    /**
     * @param object $ownerEntity
     * @param object|null $customer
     */
    public function setCustomer($ownerEntity, $customer)
    {
        $this->unsetCustomer($ownerEntity);

        if ($customer !== null) {
            $this->propertyAccessor->setValue(
                $ownerEntity,
                $this->getCustomerProperty(ClassUtils::getClass($ownerEntity), $customer),
                $customer
            );
        }
    }

    /**
     * @param object $ownerEntity
     *
     * @return object|null
     */
    public function getCustomer($ownerEntity)
    {
        $customerProperties = $this->getCustomerProperties(ClassUtils::getClass($ownerEntity));
        foreach ($customerProperties as $customerProperty) {
            $customer = $this->propertyAccessor->getValue($ownerEntity, $customerProperty);
            if ($customer) {
                return $customer;
            }
        }

        return null;
    }

    /**
     * @param object $ownerEntity
     */
    protected function unsetCustomer($ownerEntity)
    {
        $customerProperties = $this->getCustomerProperties(ClassUtils::getClass($ownerEntity));
        foreach ($customerProperties as $customerProperty) {
            $this->propertyAccessor->setValue($ownerEntity, $customerProperty, null);
        }
    }

    /**
     * @param string $ownerEntityClass
     * @param object $customer
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getCustomerProperty($ownerEntityClass, $customer)
    {
        $customerProperties = $this->getCustomerProperties($ownerEntityClass);
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
    protected function getCustomerProperties($entityClass)
    {
        // this is because of tests
        $customers = $this->salesConfigProvider->getConfig($entityClass)->get('customers');
        if ($customers) {
            return $customers;
        }

        // todo: do not hardcode
        $config = [
            'Oro\Bundle\SalesBundle\Entity\Opportunity' => [
                'Oro\Bundle\MagentoBundle\Entity\Customer' => 'customer1c6b2c05',
            ],
            'Oro\Bundle\SalesBundle\Entity\Lead' => [
                'Oro\Bundle\MagentoBundle\Entity\Customer' => 'customer1c6b2c05',
            ],
        ];
        return $config[$entityClass];
    }
}
