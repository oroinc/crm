<?php

namespace Oro\Bundle\SalesBundle\Form\DataTransformer;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CustomerToStringTransformer implements DataTransformerInterface
{
    /** @var DataTransformerInterface */
    protected $entityToStringTransformer;

    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    public function __construct(DataTransformerInterface $entityToStringTransformer, AccountCustomerManager $manager)
    {
        $this->entityToStringTransformer = $entityToStringTransformer;
        $this->accountCustomerManager    = $manager;
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
            $accountCustomerManagerClass = get_class($this->accountCustomerManager);

            return $accountCustomerManagerClass::createCustomer((new Account())->setName($data['value']));
        }

        $target = $this->entityToStringTransformer->reverseTransform($value);

        return $this->accountCustomerManager->getAccountCustomerByTarget($target);
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
