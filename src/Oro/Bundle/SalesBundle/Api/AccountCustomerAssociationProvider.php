<?php

namespace Oro\Bundle\SalesBundle\Api;

use Oro\Bundle\ApiBundle\Provider\ResourcesProvider;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\ApiBundle\Util\ValueNormalizerUtil;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Provides information about associations of customer entities with the account entity.
 */
class AccountCustomerAssociationProvider implements ResetInterface
{
    private const KEY_DELIMITER = '|';

    private array $customerAssociationNames;
    private ConfigProvider $configProvider;
    private ValueNormalizer $valueNormalizer;
    private ResourcesProvider $resourcesProvider;
    private array $customerAssociations = [];

    public function __construct(
        array $customerAssociationNames,
        ConfigProvider $configProvider,
        ValueNormalizer $valueNormalizer,
        ResourcesProvider $resourcesProvider
    ) {
        $this->customerAssociationNames = $customerAssociationNames;
        $this->configProvider = $configProvider;
        $this->valueNormalizer = $valueNormalizer;
        $this->resourcesProvider = $resourcesProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function reset()
    {
        $this->customerAssociations = [];
    }

    public function isCustomerEntity(string $entityClass): bool
    {
        return $this->configProvider->isCustomerClass($entityClass);
    }

    public function getAccountCustomerAssociations(string $version, RequestType $requestType): array
    {
        $cacheKey = (string)$requestType . self::KEY_DELIMITER . $version;
        if (isset($this->customerAssociations[$cacheKey])) {
            return $this->customerAssociations[$cacheKey];
        }

        $result = [];
        $customerEntityClasses = $this->configProvider->getCustomerClasses();
        foreach ($customerEntityClasses as $customerEntityClass) {
            if ($this->resourcesProvider->isResourceAccessible($customerEntityClass, $version, $requestType)) {
                $result[$this->getAssociationName($customerEntityClass, $requestType)] = [
                    'className'       => $customerEntityClass,
                    'associationName' => $this->getCustomerTargetAssociationName($customerEntityClass)
                ];
            }
        }
        $this->customerAssociations[$cacheKey] = $result;

        return $result;
    }

    public function getCustomerTargetAssociationName(string $customerEntityClass): string
    {
        return AccountCustomerManager::getCustomerTargetField($customerEntityClass);
    }

    private function getAssociationName(string $entityClass, RequestType $requestType): string
    {
        return $this->customerAssociationNames[$entityClass]
            ?? ValueNormalizerUtil::convertToEntityType($this->valueNormalizer, $entityClass, $requestType);
    }
}
