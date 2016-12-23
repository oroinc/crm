<?php

namespace Oro\Bundle\SalesBundle\Form\Guesser;

use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\TypeGuess;

use Oro\Bundle\ApiBundle\Collection\IncludedEntityCollection;
use Oro\Bundle\ApiBundle\Metadata\EntityMetadata;
use Oro\Bundle\ApiBundle\Metadata\MetadataAccessorInterface;

class CustomerApiTypeGuesser implements FormTypeGuesserInterface
{
    /** @var MetadataAccessorInterface|null */
    protected $metadataAccessor;

    /** @var IncludedEntityCollection|null */
    protected $includedEntities;

    /** @var string */
    protected $customerAssociationField;

    /**
     * @return MetadataAccessorInterface|null
     */
    public function getMetadataAccessor()
    {
        return $this->metadataAccessor;
    }

    /**
     * @param MetadataAccessorInterface|null $metadataAccessor
     */
    public function setMetadataAccessor(MetadataAccessorInterface $metadataAccessor = null)
    {
        $this->metadataAccessor = $metadataAccessor;
    }

    /**
     * @return IncludedEntityCollection|null
     */
    public function getIncludedEntities()
    {
        return $this->includedEntities;
    }

    /**
     * @param IncludedEntityCollection|null $includedEntities
     */
    public function setIncludedEntities(IncludedEntityCollection $includedEntities = null)
    {
        $this->includedEntities = $includedEntities;
    }

    /**
     * @param string $customerAssociationField
     */
    public function setCustomerAssociationField($customerAssociationField)
    {
        $this->customerAssociationField = $customerAssociationField;
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        if ($this->customerAssociationField === $property) {
            $metadata = $this->getMetadataForClass($class);
            if ($metadata) {
                $associationMetadata = $metadata->getAssociation($this->customerAssociationField);
                if ($associationMetadata) {
                    return new TypeGuess(
                        'oro_sales_customer_api',
                        [
                            'metadata' => $associationMetadata,
                            'included_entities' => $this->includedEntities
                        ],
                        TypeGuess::HIGH_CONFIDENCE
                    );
                }
            }
        }

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
