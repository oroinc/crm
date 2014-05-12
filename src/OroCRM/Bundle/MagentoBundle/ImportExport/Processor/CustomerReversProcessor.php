<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Processor;

use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerReverseProcessor implements ProcessorInterface, ContextAwareInterface
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @param Customer $customer
     *
     * @return array
     */
    public function process($customer)
    {
        $result = [];

        if ($customer->getContact()) {

            if ($this->isEmailChanged($customer)) {
                $result['email'] = $customer->getEmail();
            }

            if ($this->isFirstNameChanged($customer)) {
                $result['email'] = $customer->getEmail();
            }

        }

        return $result;
    }

    /**
     * @param ContextInterface $context
     * @throws InvalidConfigurationException
     */
    public function setImportExportContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @param $customer
     *
     * @return bool
     */
    protected function isEmailChanged(Customer $customer)
    {
        return $customer->getEmail() !== $customer->getContact()->getPrimaryEmail();
    }

    /**
     * @param Customer $customer
     *
     * @return bool
     */
    protected function isFirstNameChanged(Customer $customer)
    {
        return $customer->getFirstName() !== $customer->getContact()->getFirstName();
    }
}
