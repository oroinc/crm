<?php

namespace Oro\Bundle\ChannelBundle\Entity\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Component\DoctrineUtils\ORM\SqlQueryBuilder;
use Oro\Component\DoctrineUtils\ORM\UnionQueryBuilder;
use Oro\Bundle\SearchBundle\Engine\Indexer as SearchIndexer;
use Oro\Bundle\SearchBundle\Query\Result as SearchResult;
use Oro\Bundle\SearchBundle\Query\Result\Item as SearchResultItem;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class CustomerSearchApiEntityManager extends ApiEntityManager
{
    const DEFAULT_CHANNEL_FIELD_NAME = 'dataChannel';

    const CHANNEL_ENTITY_CLASS = 'Oro\Bundle\ChannelBundle\Entity\Channel';

    /** @var SearchIndexer */
    protected $searchIndexer;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var SettingsProvider */
    protected $settings;

    /**
     * {@inheritdoc}
     * @param SearchIndexer $searchIndexer
     * @param EventDispatcherInterface $dispatcher
     * @param SettingsProvider $settings
     */
    public function __construct(
        $class,
        ObjectManager $om,
        SearchIndexer $searchIndexer,
        EventDispatcherInterface $dispatcher,
        SettingsProvider $settings
    ) {
        parent::__construct($class, $om);
        $this->searchIndexer = $searchIndexer;
        $this->dispatcher = $dispatcher;
        $this->settings = $settings;
    }

    /**
     * Gets search result
     *
     * @param int           $page   Page number
     * @param int           $limit  Number of items per page
     * @param string        $search The search string.
     * @param Criteria|null $criteria
     *
     * @return array
     */
    public function getSearchResult($page = 1, $limit = 10, $search = '', $criteria = null)
    {
        $searchQuery = $this->searchIndexer->getSimpleSearchQuery(
            $search,
            $this->getOffset($page, $limit),
            $limit,
            $this->getCustomerSearchAliases()
        );

        if ($criteria && $expression = $criteria->getWhereExpression()) {
            $searchQuery->getCriteria()->andWhere($expression);
        }

        $searchResult = $this->searchIndexer->query($searchQuery);

        $result = [
            'result'     => [],
            'totalCount' =>
                function () use ($searchResult) {
                    return $searchResult->getRecordsCount();
                }
        ];

        if ($searchResult->count() > 0) {
            $customers = $this->getCustomerListQueryBuilder($searchResult)->getQuery()->getResult();

            $result['result'] = $this->mergeResults($searchResult, $customers);
        }

        return $result;
    }

    /**
     * Merges the search result and customers
     *
     * @param SearchResult $searchResult
     * @param array        $customers
     *
     * @return array
     */
    protected function mergeResults(SearchResult $searchResult, array $customers)
    {
        $result = [];

        /** @var SearchResultItem $item */
        foreach ($searchResult as $item) {
            $this->dispatcher->dispatch(PrepareResultItemEvent::EVENT_NAME, new PrepareResultItemEvent($item));

            $id        = (int)$item->getRecordId();
            $className = $item->getEntityName();

            $resultItem = [
                'id'      => $id,
                'entity'  => $className,
                'title'   => $item->getRecordTitle(),
                'channel' => null
            ];

            foreach ($customers as $customer) {
                if ($customer['entity'] === $className && $customer['id'] === $id) {
                    $resultItem['channel'] = $customer['channelId'];
                    $resultItem['accountName'] = $customer['accountName'];
                    break;
                }
            }

            $result[] = $resultItem;
        }

        return $result;
    }

    /**
     * Returns a query builder that could be used for fetching the list of the Customer entities
     * filtered by ids.
     *
     * @param SearchResult $searchResult
     *
     * @return SqlQueryBuilder
     */
    protected function getCustomerListQueryBuilder(SearchResult $searchResult)
    {
        /** @var EntityManager $em */
        $em = $this->getObjectManager();

        $qb = new UnionQueryBuilder($em);
        $qb
            ->addSelect('channelId', 'channelId', Type::INTEGER)
            ->addSelect('entityId', 'id', Type::INTEGER)
            ->addSelect('entityClass', 'entity')
            ->addSelect('accountName', 'accountName');
        foreach ($this->getCustomerListFilters($searchResult) as $customerClass => $customerIds) {
            $subQb = $em->getRepository($customerClass)->createQueryBuilder('e')
                ->select(
                    sprintf(
                        'channel.id AS channelId, e.id AS entityId, \'%s\' AS entityClass, account.name as accountName',
                        $customerClass
                    )
                )
                ->innerJoin('e.' . $this->getChannelFieldName($customerClass), 'channel')
                ->leftJoin('e.account', 'account');
            $subQb->where($subQb->expr()->in('e.id', $customerIds));
            $qb->addSubQuery($subQb->getQuery());
        }

        return $qb->getQueryBuilder();
    }

    /**
     * Extracts ids of the Customer entities from a given search result
     *
     * @param SearchResult $searchResult
     *
     * @return array example: ['Acme\Entity\Customer' => [1, 2, 3], ...]
     */
    protected function getCustomerListFilters(SearchResult $searchResult)
    {
        $filters = [];
        /** @var SearchResultItem $item */
        foreach ($searchResult as $item) {
            $entityClass = $item->getEntityName();
            if (!isset($filters[$entityClass])) {
                $filters[$entityClass] = [];
            }
            $filters[$entityClass][] = $item->getRecordId();
        }

        return $filters;
    }

    /**
     * Gets the field name for many-to-one relation between the Customer the Channel entities
     *
     * @param string $customerClass The FQCN of the Customer entity
     *
     * @return string
     *
     * @throws \RuntimeException if the relation not found
     */
    protected function getChannelFieldName($customerClass)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $this->getObjectManager()->getClassMetadata($customerClass);
        if ($metadata->hasAssociation(self::DEFAULT_CHANNEL_FIELD_NAME)
            && $metadata->getAssociationTargetClass(self::DEFAULT_CHANNEL_FIELD_NAME) === self::CHANNEL_ENTITY_CLASS
        ) {
            return self::DEFAULT_CHANNEL_FIELD_NAME;
        }

        $channelAssociations = $metadata->getAssociationsByTargetClass(self::CHANNEL_ENTITY_CLASS);
        foreach ($channelAssociations as $fieldName => $mapping) {
            if ($mapping['type'] === ClassMetadata::MANY_TO_ONE && $mapping['isOwningSide']) {
                return $fieldName;
            }
        }

        throw new \RuntimeException(
            sprintf(
                'The entity "%s" must have many-to-one relation to "%s".',
                $customerClass,
                self::CHANNEL_ENTITY_CLASS
            )
        );
    }

    /**
     * Gets all class names for all the Customer entities
     *
     * @return string[]
     */
    protected function getCustomerEntities()
    {
        return array_map(
            function (ClassMetadata $metadata) {
                return $metadata->name;
            },
            array_filter(
                $this->getObjectManager()->getMetadataFactory()->getAllMetadata(),
                function (ClassMetadata $metadata) {
                    // @todo: should be removed in CRM-3263
                    if ($metadata->name === 'Oro\Bundle\ChannelBundle\Entity\CustomerIdentity') {
                        return false;
                    }

                    return
                        !$metadata->isMappedSuperclass
                        && $this->settings->isCustomerEntity($metadata->getReflectionClass()->getName());
                }
            )
        );
    }

    /**
     * Returns search aliases for all the Customer entities
     *
     * @return string[]
     */
    protected function getCustomerSearchAliases()
    {
        return array_values(
            $this->searchIndexer->getEntityAliases($this->getCustomerEntities())
        );
    }
}
