<?php

namespace Oro\Bundle\SalesBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use Oro\Bundle\SalesBundle\Provider\Customer\AccountCustomerHelper;
use Oro\Bundle\SalesBundle\Entity\Customer;

class CustomerToStringTransformer implements DataTransformerInterface
{
    /** @var DataTransformerInterface */
    protected $entityToStringTransformer;

    /** @var AccountCustomerHelper */
    protected $accountCustomerHelper;

    /**
     * @param DataTransformerInterface $entityToStringTransformer
     * @param AccountCustomerHelper    $helper
     */
    public function __construct(DataTransformerInterface $entityToStringTransformer, AccountCustomerHelper $helper)
    {
        $this->entityToStringTransformer = $entityToStringTransformer;
        $this->accountCustomerHelper     = $helper;
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
            return AccountCustomerHelper::createCustomerFromAccount(null, $data['value']);
        }

        $target = $this->entityToStringTransformer->reverseTransform($value);

        return $this->accountCustomerHelper->getOrCreateAccountCustomerByTarget($target);
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

            $value = $this->accountCustomerHelper->getTargetCustomerOrAccount($value);
        }

        return $this->entityToStringTransformer->transform($value);
    }
}
