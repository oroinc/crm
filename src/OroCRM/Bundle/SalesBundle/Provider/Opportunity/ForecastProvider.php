<?php

namespace OroCRM\Bundle\SalesBundle\Provider\Opportunity;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\QueryDesignerBundle\QueryDesigner\FilterProcessor;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\UserBundle\Entity\Repository\UserRepository;
use Oro\Component\DoctrineUtils\ORM\QueryUtils;

use OroCRM\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;

class ForecastProvider
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var EnumValueProvider */
    protected $enumProvider;

    /** @var  array */
    protected $data;

    /** @var  array */
    protected $statuses;

    /** @var FilterProcessor */
    protected $filterProcessor;

    /** @var array */
    protected static $fieldsAuditMap = [
        'status'       => ['old' => 'oldText', 'new' => 'newText'],
        'owner'        => ['old' => 'oldText', 'new' => 'newText'],
        'closeDate'    => ['old' => 'oldDatetime', 'new' => 'newDatetime'],
        'probability'  => ['old' => 'oldFloat', 'new' => 'newFloat'],
        'budgetAmount' => ['old' => 'oldFloat', 'new' => 'newFloat'],
    ];

    /**
     * @param RegistryInterface $doctrine
     * @param AclHelper         $aclHelper
     * @param EnumValueProvider $enumProvider
     * @param FilterProcessor   $filterProcessor
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        EnumValueProvider $enumProvider,
        FilterProcessor $filterProcessor
    ) {
        $this->doctrine        = $doctrine;
        $this->aclHelper       = $aclHelper;
        $this->enumProvider    = $enumProvider;
        $this->filterProcessor = $filterProcessor;
    }

    /**
     * @param array          $ownerIds
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @param \DateTime|null $moment
     * @param array|null     $queryFilter
     *
     * @return array ['inProgressCount' => <int>, 'budgetAmount' => <double>, 'weightedForecast' => <double>]
     */
    public function getForecastData(
        array $ownerIds,
        \DateTime $start = null,
        \DateTime $end = null,
        \DateTime $moment = null,
        array $queryFilter = null
    ) {
        $filters = isset($queryFilter['definition'])
            ? json_decode($queryFilter['definition'], true)
            : [];
        $key     = $this->getDataHashKey($ownerIds, $start, $end, $moment, $filters);
        if (!isset($this->data[$key])) {
            if (!$moment) {
                $this->data[$key] = $this->getCurrentData($ownerIds, $start, $end, $filters);
            } else {
                $this->data[$key] = $this->getMomentData($ownerIds, $moment, $start, $end, $filters);
            }
        }

        return $this->data[$key];
    }

    /**
     * @param array     $ownerIds
     * @param \DateTime $start
     * @param \DateTime $end
     * @param array     $filters
     *
     * @return array
     */
    protected function getCurrentData(
        array $ownerIds,
        \DateTime $start = null,
        \DateTime $end = null,
        array $filters = []
    ) {
        $clonedStart = $start ? clone $start : null;
        $clonedEnd   = $end ? clone $end : null;
        $alias       = 'o';
        $qb          = $this->getOpportunityRepository()->getForecastQB($alias);

        $qb = $this->filterProcessor
            ->process($qb, 'OroCRM\Bundle\SalesBundle\Entity\Opportunity', $filters, $alias);

        if (!empty($ownerIds)) {
            $qb->join('o.owner', 'owner');
            QueryUtils::applyOptimizedIn($qb, 'owner.id', $ownerIds);
        }
        $this->applyDateFiltering($qb, 'o.closeDate', $clonedStart, $clonedEnd);
        $this->applyProbabilityCondition($qb, 'o');

        return $this->aclHelper->apply($qb)->getOneOrNullResult();
    }

    /**
     * @param array          $ownerIds
     * @param \DateTime      $moment
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @param array          $filters
     *
     * @return array
     */
    protected function getMomentData(
        array $ownerIds,
        \DateTime $moment,
        \DateTime $start = null,
        \DateTime $end = null,
        array $filters = []
    ) {
        $qb = $this->getAuditRepository()->createQueryBuilder('a');
        $qb
            ->select(<<<SELECT
(SELECT afps.newFloat FROM OroDataAuditBundle:AuditField afps WHERE afps.id = MAX(afp.id)) AS probability,
(SELECT afpb.newFloat FROM OroDataAuditBundle:AuditField afpb WHERE afpb.id = MAX(afb.id)) AS budgetAmount
SELECT
            )
            ->join('a.fields', 'afs', Join::WITH, 'afs.field = :statusField')
            ->join('a.fields', 'afc', Join::WITH, 'afc.field = :closeDateField')
            ->join('a.fields', 'afp', Join::WITH, 'afp.field = :probabilityField')
            ->join('a.fields', 'afb', Join::WITH, 'afb.field = :budgetAmountField')
            ->where('a.objectClass = :objectClass AND a.loggedAt < :moment')
            ->groupBy('a.objectId')
            ->having(<<<HAVING
EXISTS(
    SELECT
        afsh.newText
    FROM OroDataAuditBundle:AuditField afsh
    WHERE
        afsh.id = MAX(afs.id)
        AND afsh.newText NOT IN (:excludedStatuses)
)
AND EXISTS(
    SELECT
        afph.newFloat
    FROM OroDataAuditBundle:AuditField afph
    WHERE
        afph.id = MAX(afp.id)
        AND (afph.newFloat NOT IN (:excludedProbabilities) or afph.newFloat IS NULL)
)
HAVING
            )
            ->setParameters([
                'objectClass' => 'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                'statusField' => 'status',
                'closeDateField' => 'closeDate',
                'probabilityField' => 'probability',
                'budgetAmountField' => 'budgetAmount',
                'excludedProbabilities' => [0, 1],
                'excludedStatuses' => [
                    $this->getStatusTextValue('lost'),
                    $this->getStatusTextValue('won'),
                ],
                'moment' => $moment,
            ]);

        $this->applyHistoryDateFiltering($qb, $start, $end);

        if ($ownerIds) {
            $qb
                ->join('a.fields', 'afo', Join::WITH, 'afo.field = :ownerField')
                ->andHaving(<<<HAVING
EXISTS(
    SELECT
        afoh.newText
    FROM OroDataAuditBundle:AuditField afoh
    WHERE
        afoh.id = MAX(afo.id)
        AND afoh.newText IN (SELECT u.username FROM OroUserBundle:User u WHERE u.id IN (:ownerIds))
)
HAVING
                )
                ->setParameter('ownerField', 'owner')
                ->setParameter('ownerIds', $ownerIds);
        }

        if ($filters) {
            $qb
                ->join('OroCRMSalesBundle:Opportunity', 'o', Join::WITH, 'a.objectId = o.id');
            $this->queryProcessor
                ->process($qb, 'OroCRM\Bundle\SalesBundle\Entity\Opportunity', $filters, 'o');
        }

        $result = $qb
            ->getQuery()
            ->getArrayResult();

        return array_reduce(
            $result,
            function ($result, $row) {
                $result['inProgressCount']++;
                $result['budgetAmount'] += $row['budgetAmount'];
                $result['weightedForecast'] += $row['budgetAmount'] * $row['probability'];

                return $result;
            },
            ['inProgressCount' => 0, 'budgetAmount' => 0, 'weightedForecast' => 0]
        );
    }

    /**
     * @param QueryBuilder $qb
     * @param \DateTime $start
     * @param \DateTime $end
     */
    protected function applyHistoryDateFiltering(QueryBuilder $qb, \DateTime $start = null, \DateTime $end = null)
    {
        if (!$start && !$end) {
            return;
        }

        $closeDateFieldQb = $this->getAuditFieldRepository()->createQueryBuilder('afch')
            ->select('afch.newDate')
            ->where('afch.id = MAX(afc.id)');
        $this->applyDateFiltering($closeDateFieldQb, 'afch.newDate', $start, $end);

        $qb->andHaving($qb->expr()->exists($closeDateFieldQb->getDQL()));
        foreach ($closeDateFieldQb->getParameters() as $parameter) {
            $qb->setParameter(
                $parameter->getName(),
                $parameter->getValue(),
                $parameter->getType()
            );
        }
    }

    /**
     * @param QueryBuilder   $qb
     * @param string         $field
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     */
    protected function applyDateFiltering(
        QueryBuilder $qb,
        $field,
        \DateTime $start = null,
        \DateTime $end = null
    ) {
        if ($start) {
            $qb
                ->andWhere(sprintf('%s >= :start', $field))
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere(sprintf('%s < :end', $field))
                ->setParameter('end', $end);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $alias
     */
    protected function applyProbabilityCondition(QueryBuilder $qb, $alias)
    {
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->andX(
                    sprintf('%s.probability <> 0', $alias),
                    sprintf('%s.probability <> 1', $alias)
                ),
                sprintf('%s.probability is NULL', $alias)
            )
        );
    }

    /**
     * @return OpportunityRepository
     */
    protected function getOpportunityRepository()
    {
        return $this->doctrine->getRepository('OroCRMSalesBundle:Opportunity');
    }

    /**
     * @return EntityRepository
     */
    protected function getAuditFieldRepository()
    {
        return $this->doctrine->getRepository('OroDataAuditBundle:AuditField');
    }

    /**
     * @return EntityRepository
     */
    protected function getAuditRepository()
    {
        return $this->doctrine->getRepository('OroDataAuditBundle:Audit');
    }

    /**
     * @return UserRepository
     */
    protected function getUserRepository()
    {
        return $this->doctrine->getRepository('OroUserBundle:User');
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    protected function getStatusTextValue($key)
    {
        if (null === $this->statuses) {
            $this->statuses = $this->enumProvider->getEnumChoicesByCode('opportunity_status');
        }

        return $this->statuses[$key];
    }

    /**
     * @param array          $ownerIds
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @param \DateTime|null $moment
     * @param array          $filters
     *
     * @return string
     */
    protected function getDataHashKey(
        array $ownerIds,
        \DateTime $start = null,
        \DateTime $end = null,
        \DateTime $moment = null,
        array $filters = []
    ) {
        return md5(
            serialize(
                [
                    'ownerIds' => $ownerIds,
                    'start'    => $start,
                    'end'      => $end,
                    'moment'   => $moment,
                    'filters'  => $filters
                ]
            )
        );
    }
}
