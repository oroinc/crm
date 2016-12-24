<?php

namespace Oro\Bundle\SalesBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

class CustomerApiTransformer implements DataTransformerInterface
{
    /** @var DataTransformerInterface */
    protected $innerTransformer;

    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    /**
     * @param DataTransformerInterface $innerTransformer
     * @param AccountCustomerManager   $manager
     */
    public function __construct(DataTransformerInterface $innerTransformer, AccountCustomerManager $manager)
    {
        $this->innerTransformer       = $innerTransformer;
        $this->accountCustomerManager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        $target   = $this->innerTransformer->reverseTransform($value);
        if (!$target) {
            return $target;
        }
        $customer = $this->accountCustomerManager->getAccountCustomerByTarget($target, false);
        if (!$customer) {
            $account  = $this->accountCustomerManager->createAccountForTarget($target);
            $customer = AccountCustomerManager::createCustomer($account, $target);
        }

        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $this->innerTransformer->transform($value);
    }
}
