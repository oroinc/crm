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

        if ($customer->getChannel()) {
            $result['channel'] = $customer->getChannel();
        }

        if ($customer->getContact()) {

            if ($this->isEmailChanged($customer)) {
                $result['object']['email'] = $customer->getEmail();
            }

            if ($this->isFirstNameChanged($customer)) {
                $result['object']['firstname'] = $customer->getEmail();
            }

            if ($this->isLastNameChanged($customer)) {
                $result['object']['lastname'] = $customer->getEmail();
            }

            if ($this->isNamePreffixChanged($customer)) {
                $result['object']['prefix'] = $customer->getNamePrefix();
            }

            if ($this->isNameSuffixChanged($customer)) {
                $result['object']['prefix'] = $customer->getNameSuffix();
            }

            if ($this->isBirthdayChanged($customer)) {
                $result['object']['dob'] = $customer->getBirthday();
            }

            if ($this->isGenderChanged($customer)) {
                $result['object']['gender'] = $customer->getGender();
            }

            if ($this->isMiddleNameChanged($customer)) {
                $result['object']['middlename'] = $customer->getMiddleName();
            }
        }

        return (object)$result;
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

    /**
     * @param Customer $customer
     *
     * @return bool
     */
    protected function isLastNameChanged(Customer $customer)
    {
        return $customer->getLastName() !== $customer->getContact()->getLastName();
    }

    /**
     * @param Customer $customer
     *
     * @return bool
     */
    protected function isNamePreffixChanged(Customer $customer)
    {
        return $customer->getNamePrefix() === $customer->getContact()->getNamePrefix();
    }

    /**
     * @param Customer $customer
     *
     * @return bool
     */
    protected function isNameSuffixChanged(Customer $customer)
    {
        return $customer->getNameSuffix() === $customer->getContact()->getNameSuffix();
    }

    /**
     * @param Customer $customer
     *
     * @return bool
     */
    protected function isBirthdayChanged(Customer $customer)
    {
        return $customer->getBirthday() === $customer->getContact()->getBirthday();
    }

    /**
     * @param Customer $customer
     *
     * @return bool
     */
    protected function isGenderChanged(Customer $customer)
    {
        return $customer->getGender() === $customer->getContact()->getGender();
    }

    /**
     * @param Customer $customer
     *
     * @return bool
     */
    protected function isMiddleNameChanged(Customer $customer)
    {
        return $customer->getMiddleName() === $customer->getContact()->getMiddleName();
    }
}
