<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnector;
use OroCRM\Bundle\MagentoBundle\Provider\Dependency\CustomerDependencyManager;

class CustomerInfoReader extends CustomerConnector
{
    /** @var string */
    protected $customerClassName;

    /** @var bool[] */
    protected $loaded = [];

    /**
     * @param string $customerClassName
     */
    public function setCustomerClassName($customerClassName)
    {
        $this->customerClassName = $customerClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $customer = $this->getCustomer();
        $originId = $this->getCustomer()->getOriginId();

        if (empty($originId) || !empty($this->loaded[$originId])) {
            return null;
        }

        $result = $this->transport->getCustomerInfo($customer);
        $result->addresses = $this->transport->getCustomerAddresses($customer);
        foreach ($result->addresses as $key => $val) {
            $result->addresses[$key] = (array)$val;
        }

        $this->loaded[$originId] = true;

        CustomerDependencyManager::addDependencyData($result, $this->transport);

        return ConverterUtils::objectToArray($result);
    }

    /**
     * @return Customer
     */
    protected function getCustomer()
    {
        $configuration = $this->getContext()->getConfiguration();

        if (empty($configuration['data'])) {
            throw new \InvalidArgumentException('Data is missing');
        }

        $customer = $configuration['data'];

        if (!$customer instanceof Customer) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Instance of "%s" expected, "%s" given.',
                    $this->customerClassName,
                    is_object($customer) ? get_class($customer) : gettype($customer)
                )
            );
        }

        return $customer;
    }
}
