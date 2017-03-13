<?php

namespace Oro\Bundle\SalesBundle\Form\Guesser;

use Oro\Bundle\ApiBundle\Metadata\EntityMetadata;
use Oro\Bundle\ApiBundle\Metadata\MetadataAccessorInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\TypeGuess;

class CustomerAccountApiTypeGuesser implements FormTypeGuesserInterface
{
    /** @var MetadataAccessorInterface|null */
    protected $metadataAccessor;

    /**
     * @param MetadataAccessorInterface|null $metadataAccessor
     */
    public function setMetadataAccessor(MetadataAccessorInterface $metadataAccessor = null)
    {
        $this->metadataAccessor = $metadataAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function guessMaxLength($class, $property)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function guessPattern($class, $property)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function guessRequired($class, $property)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        if ($property !== 'account') {
            return null;
        }

        $metadata = $this->getMetadataForClass($class);
        if (!$metadata) {
            return null;
        }

        $associationMetadata = $metadata->getAssociation('account');
        if (!$associationMetadata) {
            return null;
        }

        return new TypeGuess(
            'oro_sales_customer_account_api',
            [
                'metadata' => $associationMetadata,
            ],
            TypeGuess::HIGH_CONFIDENCE
        );
    }

    /**
     * @param string $class
     *
     * @return EntityMetadata|null
     */
    protected function getMetadataForClass($class)
    {
        return null !== $this->metadataAccessor
            ? $this->metadataAccessor->getMetadata($class)
            : null;
    }
}
