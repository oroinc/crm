<?php

namespace Oro\Bundle\SalesBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\AccountBundle\Entity\Account;

class CustomerToStringTransformer implements DataTransformerInterface
{
    /** @var DataTransformerInterface */
    protected $entityToStringTransformer;

    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    /**
     * @param DataTransformerInterface $entityToStringTransformer
     * @param AccountCustomerManager    $manager
     */
    public function __construct(DataTransformerInterface $entityToStringTransformer, AccountCustomerManager $manager)
    {
        $this->entityToStringTransformer = $entityToStringTransformer;
        $this->accountCustomerManager = $manager;
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
            return AccountCustomerManager::createCustomerFromAccount((new Account())->setName($data['value']));
        }

        $target = $this->entityToStringTransformer->reverseTransform($value);

        return $this->accountCustomerManager->getOrCreateAccountCustomerByTarget($target);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value instanceof Customer) {
            $account = $value->getAccount();
            // new accounts values transforms directly
            if (!$account->getId()) {
                return json_encode([
                    'value' => $account->getName(),
                ]);
            }

            $value = AccountCustomerManager::getTargetCustomerOrAccount($value);
        }

        return $this->entityToStringTransformer->transform($value);
    }
}
