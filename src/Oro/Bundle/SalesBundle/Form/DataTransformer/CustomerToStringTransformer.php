<?php

namespace Oro\Bundle\SalesBundle\Form\DataTransformer;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Factory\CustomerFactory;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms a value between a Customer entity and its string representation.
 */
class CustomerToStringTransformer implements DataTransformerInterface
{
    public function __construct(
        private DataTransformerInterface $entityToStringTransformer,
        private AccountCustomerManager $accountCustomerManager,
        private CustomerFactory $customerFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        $data = json_decode($value, true);
        if (!is_array($data)) {
            throw new TransformationFailedException('Expected an array after decoding a string.');
        }

        if (!empty($data['value'])) {
            $account = new Account();
            $account->setName($data['value']);
            $customer = $this->customerFactory->createCustomer();
            $customer->setTarget($account, null);

            return $customer;
        }

        return $this->accountCustomerManager->getAccountCustomerByTarget(
            $this->entityToStringTransformer->reverseTransform($value)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value instanceof Customer) {
            $value = $value->getTarget();
        }

        return $this->entityToStringTransformer->transform($value);
    }
}
