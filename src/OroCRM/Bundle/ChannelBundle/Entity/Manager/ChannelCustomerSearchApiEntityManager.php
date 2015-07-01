<?php
namespace OroCRM\Bundle\ChannelBundle\Entity\Manager;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

use Oro\Bundle\EntityBundle\ORM\QueryBuilderHelper;
use Oro\Bundle\EntityBundle\ORM\QueryUtils;
use Oro\Bundle\EntityBundle\ORM\SqlQueryBuilder;
use Oro\Bundle\EntityBundle\Exception\EntityAliasNotFoundException;

use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\SearchBundle\Engine\Indexer as SearchIndexer;

class ChannelCustomerSearchApiEntityManager extends ApiEntityManager
{
    const DATA_CHANNEL_FIELD_NAME = 'dataChannel';

    const DATA_CHANNEL_ENTITY_CLASS = 'OroCRM\Bundle\ChannelBundle\Entity\Channel';

    const CUSTOMER_IDENTITY_INTERFACE = 'OroCRM\Bundle\ChannelBundle\Model\CustomerIdentityInterface';

    /** @var SearchIndexer */
    protected $searchIndexer;

    /**
     * {@inheritdoc}
     * @param SearchIndexer $searchIndexer
     */
    public function __construct(
        $class,
        ObjectManager $om,
        SearchIndexer $searchIndexer
    ) {
        parent::__construct($class, $om);
        $this->searchIndexer = $searchIndexer;
    }

    /**
     * Gets search result
     *
     * @param int    $page   Page number
     * @param int    $limit  Number of items per page
     * @param string $search The search string.
     *
     * @return array
     */
    public function getSearchResult($page = 1, $limit = 10, $search = '')
    {
        $qb           = $this->searchIndexer->getSimpleSearchQuery(
            $search,
            $this->getOffset($page, $limit),
            $limit,
            $this->getCustomersSearchAliases()
        );
        $searchResult = $this->searchIndexer->query($qb);

        $data = [
            'result'     => [],
            'totalCount' =>
                function () use ($searchResult) {
                    return $searchResult->count();
                }
        ];

        if ($searchResult->getRecordsCount() > 0) {
            /** @var  Item[] $result */
            $searchResultArray = $searchResult->toArray();

            $associationQueryBuilder = $this->getSearchQueryBuilderWithChannels($searchResultArray);
            $associationResult       = $associationQueryBuilder->getQuery()->getResult();

            $data['result'] = $this->mergeResults(
                $searchResultArray,
                $associationResult
            );
        }

        return $data;
    }

    /**
     * Merge $resultWithoutCustomers and $resultWithCustomers results into one array and returns result of merge
     *
     * @param Item[] $resultWithoutCustomers
     * @param array $resultWithCustomers
     * @return array
     */
    protected function mergeResults(array $resultWithoutCustomers, array $resultWithCustomers)
    {
        $data = [];
        foreach ($resultWithoutCustomers as $entity) {
            $id        = $entity->getRecordId();
            $className = $entity->getEntityName();
            $customer  = [
                'id'      => $id,
                'entity'  => $className,
                'title'   => $entity->getRecordTitle(),
                'channel' => null,
            ];
            foreach ($resultWithCustomers as $result) {
                if ($result['entity'] === $className && $result['id'] === $id) {
                    $customer['channel'] = $result['targetId'];
                    break;
                }
            }
            $data[] = $customer;
        }

        return $data;
    }

    /**
     * Prepares filters and owner classes depends on $searchResultArray for @see getMultiAssociationOwnersQueryBuilder()
     * and return this query builder.
     *
     * @param Item[] $searchResultArray
     *
     * @return SqlQueryBuilder
     */
    protected function getSearchQueryBuilderWithChannels(array $searchResultArray)
    {
        // Get all association entities classes and prepare filters for each entity class.
        $filters           = [];
        $associationOwners = [];
        foreach ($searchResultArray as $entity) {
            $entityClass = $entity->getEntityName();
            if (!isset($associationOwners[$entity->getEntityName()])) {
                $filters[$entityClass]           = [];
                $associationOwners[$entityClass] = self::DATA_CHANNEL_FIELD_NAME;
            }
            // Collect all ids for the $entityClass
            $filters[$entityClass][] = $entity->getRecordId();
        }

        return $this->getMultiAssociationOwnersQueryBuilder(
            self::DATA_CHANNEL_ENTITY_CLASS,
            $associationOwners,
            $filters
        );
    }

    /**
     * Returns a query builder that could be used for fetching the list of owner side entities
     * the specified $associationTargetClass associated with.
     * The $filters could be used to filter entities
     *
     * The resulting query would be something like this:
     * <code>
     * SELECT
     *       entity.entityId AS id,
     *       entity.targetId as targetId,
     *       entity.entityClass AS entity,
     * FROM (
     *      SELECT [DISTINCT]
     *          target.id AS id,
     *          e.id AS entityId,
     *          {first_owner_entity_class} AS entityClass,
     *      FROM {first_owner_entity_class} AS e
     *          INNER JOIN e.{target_field_name_for_first_owner} AS target
     *          {joins}
     *      WHERE entityId IN(...)
     *      UNION ALL
     *      SELECT [DISTINCT]
     *          target.id AS id,
     *          e.id AS entityId,
     *          {second_owner_entity_class} AS entityClass,
     *      FROM {second_owner_entity_class} AS e
     *          INNER JOIN e.{target_field_name_for_second_owner} AS target
     *          {joins}
     *      WHERE entityId IN(...)
     *      UNION ALL
     *      ... select statements for other owners
     * ) entity
     * </code>
     *
     * @param string $associationTargetClass      The FQCN of the entity that is the target side of the association
     * @param array  $associationOwners           The list of fields responsible to store associations between
     *                                            the given target and association owners
     *                                            Array format: [owner_entity_class => field_name]
     * @param array  $filters                     Filters(ids) for entities which are association owners
     *                                            e.g. ['\Owner\Class\Name1' => [1,2,3], ...]
     *
     * @return SqlQueryBuilder
     */
    public function getMultiAssociationOwnersQueryBuilder(
        $associationTargetClass,
        $associationOwners,
        $filters
    ) {
        $em                = $this->doctrineHelper->getEntityManager($associationTargetClass);
        $selectStmt        = null;
        $subQueries        = [];
        $targetIdFieldName = $this->doctrineHelper->getSingleEntityIdentifierFieldName($associationTargetClass);
        foreach ($associationOwners as $ownerClass => $fieldName) {
            $subCriteria = new Criteria();
            $subCriteria->andWhere(
                $subCriteria->expr()->in('id', $filters[$ownerClass])
            );
            $subQb = $em->getRepository($ownerClass)->createQueryBuilder('e')
                ->select(
                    sprintf(
                        'target.%s AS id, e.id AS entityId, \'%s\' AS entityClass',
                        $targetIdFieldName,
                        str_replace('\'', '\'\'', $ownerClass)
                    )
                )
                ->innerJoin('e.' . $fieldName, 'target');
            // fix of doctrine error with Same Field, Multiple Values, Criteria and QueryBuilder
            // http://www.doctrine-project.org/jira/browse/DDC-2798
            // TODO revert changes when doctrine version >= 2.5 in scope of BAP-5577
            QueryBuilderHelper::addCriteria($subQb, $subCriteria);
            // $subQb->addCriteria($criteria);
            $subQuery     = $subQb->getQuery();
            $subQueries[] = QueryUtils::getExecutableSql($subQuery);
            if (empty($selectStmt)) {
                $mapping    = QueryUtils::parseQuery($subQuery)->getResultSetMapping();
                $selectStmt = sprintf(
                    'entity.%s AS id, entity.%s as targetId, entity.%s AS entity',
                    QueryUtils::getColumnNameByAlias($mapping, 'entityId'),
                    QueryUtils::getColumnNameByAlias($mapping, 'id'),
                    QueryUtils::getColumnNameByAlias($mapping, 'entityClass')
                );
            }
        }
        $rsm = new ResultSetMapping();
        $rsm
            ->addScalarResult('id', 'id', Type::INTEGER)
            ->addScalarResult('targetId', 'targetId', Type::INTEGER)
            ->addScalarResult('entity', 'entity');
        $qb = new SqlQueryBuilder($em, $rsm);
        $qb
            ->select($selectStmt)
            ->from('(' . implode(' UNION ALL ', $subQueries) . ')', 'entity');

        return $qb;
    }

    /**
     * Get all customers metadata
     *
     * @return ClassMetadata[]|array
     */
    protected function getCustomersMetadata()
    {
        /** @var ClassMetadata[] $allMetadata */
        $allMetadata = $this->om->getMetadataFactory()->getAllMetadata();

        /** @var ClassMetadata[] $customersMetadata */
        $customersMetadata = array_filter(
            $allMetadata,
            function (ClassMetadata $metadata) {
                return
                    $metadata->getReflectionClass()->isSubclassOf(
                        self::CUSTOMER_IDENTITY_INTERFACE
                    );
            }
        );

        return $customersMetadata;
    }

    /**
     * Returns aliases for all customers entities
     *
     * @return array
     */
    protected function getCustomersSearchAliases()
    {
        $customersSearchAliases = [];
        $customersMetadata      = $this->getCustomersMetadata();
        $aliases                = $this->searchIndexer->getEntitiesListAliases();
        foreach ($customersMetadata as $metadata) {
            $entityClass = $metadata->getName();
            // @todo: case below should be removed in CRM-3263
            if ($entityClass == 'OroCRM\Bundle\ChannelBundle\Entity\CustomerIdentity') {
                continue;
            }
            if (isset($aliases[$entityClass])) {
                $customersSearchAliases[] = $aliases[$entityClass];
            } else {
                throw new EntityAliasNotFoundException(
                    sprintf('The search alias for "%s" entity not found.', $entityClass)
                );
            }
        }

        return $customersSearchAliases;
    }
}
