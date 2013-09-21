<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

class ContactMaxDataProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilderPrototype;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Set QueryBuilder that will be used in calculations
     *
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $prototypeQueryBuilder = clone $queryBuilder;
        $prototypeQueryBuilder->resetDQLParts(array('groupBy', 'having', 'orderBy'));

        $this->queryBuilderPrototype = $prototypeQueryBuilder;
    }

    /**
     * Clone prototype of QueryBuilder and return it
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        if (!$this->queryBuilderPrototype) {
            /** @var EntityRepository $repository */
            $repository = $this->managerRegistry->getRepository('OroCRMContactBundle:Contact');
            $this->queryBuilderPrototype = $repository->createQueryBuilder('contact');
        }

        return clone $this->queryBuilderPrototype;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return string
     * @throws \LogicException
     */
    protected function getRootAlias(QueryBuilder $queryBuilder)
    {
        $contactAliases = $queryBuilder->getRootAliases();
        if (empty($contactAliases)) {
            throw new \LogicException('Max data query builder must have root alias');
        }

        return current($contactAliases);
    }

    /**
     * Generate DQL to calculate maximum number of specified entities
     *
     * @param string $entityName
     * @param string $entityIdentifier
     * @return int
     */
    protected function getMaxEntitiesCount($entityName, $entityIdentifier = 'id')
    {
        $queryBuilder = $this->getQueryBuilder();
        $contactAlias = $this->getRootAlias($queryBuilder);

        $queryBuilder
            ->select("COUNT(joinedEntity.$entityIdentifier) as maxCount")
            ->join("$contactAlias.$entityName", 'joinedEntity')
            ->groupBy("$contactAlias.id")
            ->orderBy('maxCount', 'DESC')
            ->setMaxResults(1);

        $query = $queryBuilder->getQuery();
        $result = $query->getOneOrNullResult(Query::HYDRATE_ARRAY);

        return !empty($result['maxCount']) ? (int)$result['maxCount'] : 0;
    }

    /**
     * @return int
     */
    public function getMaxAccountsCount()
    {
        return $this->getMaxEntitiesCount('accounts');
    }

    /**
     * @return int
     */
    public function getMaxAddressesCount()
    {
        return $this->getMaxEntitiesCount('addresses');
    }

    /**
     * @return int
     */
    public function getMaxEmailsCount()
    {
        return $this->getMaxEntitiesCount('emails');
    }

    /**
     * @return int
     */
    public function getMaxPhonesCount()
    {
        return $this->getMaxEntitiesCount('phones');
    }

    /**
     * @return int
     */
    public function getMaxGroupsCount()
    {
        return $this->getMaxEntitiesCount('groups');
    }

    /**
     * @return int
     */
    public function getMaxAddressTypesCount()
    {
        $queryBuilder = $this->getQueryBuilder();
        $contactAlias = $this->getRootAlias($queryBuilder);

        $queryBuilder
            ->select("COUNT(contactAddressType.name) as maxCount")
            ->join("$contactAlias.addresses", 'contactAddress')
            ->join('contactAddress.types', 'contactAddressType')
            ->groupBy('contactAddress.id')
            ->orderBy('maxCount', 'DESC')
            ->setMaxResults(1);

        $query = $queryBuilder->getQuery();
        $result = $query->getOneOrNullResult(Query::HYDRATE_ARRAY);

        return !empty($result['maxCount']) ? (int)$result['maxCount'] : 0;
    }
}
