<?php

namespace Oro\Bundle\MagentoBundle\Service;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MagentoBundle\DependencyInjection\Configuration;
use Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery\DiscoveryStrategyInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AutomaticDiscovery
{
    const ROOT_ALIAS = 'e';

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $discoveryEntityClass;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var DiscoveryStrategyInterface[]
     */
    protected $strategies = [];

    /**
     * @var DiscoveryStrategyInterface
     */
    protected $defaultStrategy;

    /**
     * @var OwnershipMetadataProviderInterface
     */
    protected $ownershipMetadata;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param DiscoveryStrategyInterface $defaultStrategy
     * @param OwnershipMetadataProviderInterface $ownershipMetadata
     * @param string $discoveryEntityClass
     * @param array $configuration
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        DiscoveryStrategyInterface $defaultStrategy,
        OwnershipMetadataProviderInterface $ownershipMetadata,
        $discoveryEntityClass,
        array $configuration
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->ownershipMetadata = $ownershipMetadata;
        $this->defaultStrategy = $defaultStrategy;
        $this->discoveryEntityClass = $discoveryEntityClass;

        if (array_key_exists(Configuration::DISCOVERY_NODE, $configuration)) {
            $this->configuration = $configuration[Configuration::DISCOVERY_NODE];
        }
    }

    /**
     * @param string $fieldName
     * @param DiscoveryStrategyInterface $strategy
     */
    public function addStrategy($fieldName, DiscoveryStrategyInterface $strategy)
    {
        $this->strategies[$fieldName] = $strategy;
    }

    /**
     * @param object $entity
     * @return object|null
     */
    public function discoverSimilar($entity)
    {
        if (!$this->configuration) {
            return null;
        }

        $idName = $this->doctrineHelper->getSingleEntityIdentifierFieldName($this->discoveryEntityClass);
        $idFieldName = self::ROOT_ALIAS . '.' . $idName;

        /** @var EntityRepository $repository */
        $repository = $this->doctrineHelper->getEntityRepository($this->discoveryEntityClass);
        $qb = $repository->createQueryBuilder(self::ROOT_ALIAS)
            ->select(self::ROOT_ALIAS);

        // Apply search strategies
        $this->applyStrategies($qb, $entity);

        // Apply matcher strategy
        if ($this->configuration[Configuration::DISCOVERY_OPTIONS_KEY][Configuration::DISCOVERY_MATCH_KEY]
            === Configuration::DISCOVERY_MATCH_LATEST
        ) {
            $qb->orderBy($idFieldName, Criteria::DESC);
        }

        // Skip current entity
        $id = $this->doctrineHelper->getSingleEntityIdentifier($entity);
        if (!empty($id)) {
            $idParameter = ':' . $idName;
            $qb->andWhere($qb->expr()->neq($idFieldName, $idParameter))
                ->setParameter($idParameter, $id);
        }

        // Add organization limits
        $organizationField = $this->ownershipMetadata
            ->getMetadata(ClassUtils::getClass($entity))
            ->getOrganizationFieldName();

        if ($organizationField) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $organization = $propertyAccessor->getValue($entity, $organizationField);

            $qb
                ->andWhere(sprintf('%s.%s = :organization', self::ROOT_ALIAS, $organizationField))
                ->setParameter('organization', $organization);
        }

        // Get only 1 match
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param object $entity
     */
    protected function applyStrategies(QueryBuilder $qb, $entity)
    {
        $fields = array_keys($this->configuration[Configuration::DISCOVERY_FIELDS_KEY]);
        foreach ($fields as $fieldName) {
            $this->getStrategyForField($fieldName)
                ->apply($qb, self::ROOT_ALIAS, $fieldName, $this->configuration, $entity);
        }
    }

    /**
     * @param string $fieldName
     * @return DiscoveryStrategyInterface
     */
    protected function getStrategyForField($fieldName)
    {
        if (array_key_exists($fieldName, $this->strategies)) {
            return $this->strategies[$fieldName];
        }

        return $this->defaultStrategy;
    }
}
