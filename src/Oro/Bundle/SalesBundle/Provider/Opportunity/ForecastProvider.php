<?php

namespace Oro\Bundle\SalesBundle\Provider\Opportunity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;
use Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\SegmentBundle\Query\FilterProcessor;
use Oro\Bundle\UserBundle\Dashboard\OwnerHelper;
use Oro\Bundle\UserBundle\Entity\Repository\UserRepository;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Provides a sales forecast based on opportunities
 */
class ForecastProvider
{
    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var WidgetProviderFilterManager */
    protected $widgetProviderFilter;

    /** @var EnumValueProvider */
    protected $enumProvider;

    /** @var  array */
    protected $data;

    /** @var  array */
    protected $statuses;

    /** @var FilterProcessor */
    protected $filterProcessor;

    /** @var CurrencyQueryBuilderTransformerInterface  */
    protected $qbTransformer;

    /** @var OwnerHelper */
    protected $ownerHelper;

    /** @var array */
    protected static $fieldsAuditMap = [
        'status'       => ['old' => 'oldText',       'new' => 'newText'    ],
        'owner'        => ['old' => 'oldText',       'new' => 'newText'    ],
        'closeDate'    => ['old' => 'oldDatetime',   'new' => 'newDatetime'],
        'probability'  => ['old' => 'oldFloat',      'new' => 'newFloat'   ],
        'budgetAmount' => ['old' => 'oldFloat',      'new' => 'newFloat'   ],
    ];

    public function __construct(
        ManagerRegistry $doctrine,
        AclHelper $aclHelper,
        WidgetProviderFilterManager $widgetProviderFilter,
        EnumValueProvider $enumProvider,
        FilterProcessor $filterProcessor,
        CurrencyQueryBuilderTransformerInterface $qbTransformer,
        OwnerHelper $ownerHelper
    ) {
        $this->doctrine             = $doctrine;
        $this->aclHelper            = $aclHelper;
        $this->widgetProviderFilter = $widgetProviderFilter;
        $this->enumProvider         = $enumProvider;
        $this->filterProcessor      = $filterProcessor;
        $this->qbTransformer        = $qbTransformer;
        $this->ownerHelper          = $ownerHelper;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @param \DateTime|null $moment
     *
     * @return array ['inProgressCount' => <int>, 'budgetAmount' => <double>, 'weightedForecast' => <double>]
     */
    public function getForecastData(
        WidgetOptionBag $widgetOptions,
        \DateTime $start = null,
        \DateTime $end = null,
        \DateTime $moment = null
    ) {
        $key = $this->getDataHashKey($widgetOptions, $moment);

        if (!isset($this->data[$key])) {
            if (!$moment) {
                $this->data[$key] = $this->getCurrentData($widgetOptions, $start, $end);
            } else {
                $this->data[$key] = $this->getMomentData($widgetOptions, $moment, $start, $end);
            }
        }

        return $this->data[$key];
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return array
     */
    protected function getCurrentData(
        WidgetOptionBag $widgetOptions,
        \DateTime $start = null,
        \DateTime $end = null
    ) {
        $clonedStart = $start ? clone $start : null;
        $clonedEnd   = $end ? clone $end : null;
        $alias       = 'o';
        $qb          = $this->getOpportunityRepository()->getForecastQB($this->qbTransformer, $alias);
        $this->applyDateFiltering($qb, 'o.closeDate', $clonedStart, $clonedEnd);

        return $this->processDataQueryBuilder($qb, $widgetOptions)->getOneOrNullResult();
    }

    /**
     * @param WidgetOptionBag   $widgetOptions
     * @param \DateTime         $moment
     * @param \DateTime|null    $start
     * @param \DateTime|null    $end
     *
     * @return array
     */
    protected function getMomentData(
        WidgetOptionBag $widgetOptions,
        \DateTime $moment,
        \DateTime $start = null,
        \DateTime $end = null
    ) {
        // clone datetimes as doctrine modifies their timezone which breaks stuff
        $moment = clone $moment;
        $start  = $start ? clone $start : null;
        $end    = $end ? clone $end : null;

        $qb = $this->getDataAuditQueryBuilder($widgetOptions, $moment);
        $this->applyHistoryDateFiltering($qb, $start, $end);
        $ownerIds = $this->ownerHelper->getOwnerIds($widgetOptions);
        if ($ownerIds) {
            $this->addOwnersToDataAuditQB($qb, $ownerIds);
        }
        // need to join opportunity to properly apply acl permissions
        $qb->join('OroSalesBundle:Opportunity', 'o', Join::WITH, 'a.objectId = o.id');

        $this->applyQueryFilter($qb, $widgetOptions);

        $result = $this->aclHelper->apply($qb)->getArrayResult();

        return $this->getAggregatedResult($result);
    }

    /**
     * @param QueryBuilder $qb
     * @param \DateTime    $start
     * @param \DateTime    $end
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
                $parameter->typeWasSpecified() ? $parameter->getType() : null
            );
        }
    }

    /**
     * @param QueryBuilder   $qb
     * @param string         $field
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     *
     * Known issue with `start` and `end` date modification on +/- 1 day after moving to UTC timezone
     * will be fixed in BAP-14469
     */
    protected function applyDateFiltering(
        QueryBuilder $qb,
        $field,
        \DateTime $start = null,
        \DateTime $end = null
    ) {
        if ($start) {
            $qb
                ->andWhere(QueryBuilderUtil::sprintf('%s >= :start', $field))
                ->setParameter('start', $start, Types::DATE_MUTABLE);
        }
        if ($end) {
            $qb
                ->andWhere(QueryBuilderUtil::sprintf('%s <= :end', $field))
                ->setParameter('end', $end, Types::DATE_MUTABLE);
        }
    }

    /**
     * @return OpportunityRepository
     */
    protected function getOpportunityRepository()
    {
        return $this->doctrine->getRepository('OroSalesBundle:Opportunity');
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

        return array_search($key, $this->statuses, false);
    }

    /**
     * @param  WidgetOptionBag $widgetOptions
     *
     * @param \DateTime $moment
     * @return string
     */
    protected function getDataHashKey(WidgetOptionBag $widgetOptions, \DateTime $moment = null)
    {
        return md5(serialize([$widgetOptions, $moment]));
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param \DateTime $moment
     *
     * @return QueryBuilder
     */
    protected function getDataAuditQueryBuilder(WidgetOptionBag $widgetOptions, \DateTime $moment)
    {
        $qb = $this->getAuditRepository()->createQueryBuilder('a');
        $qb
            ->select(
                <<<SELECT
(SELECT afps.newFloat FROM OroDataAuditBundle:AuditField afps WHERE afps.id = MAX(afp.id)) AS probability,
(SELECT afpb.newFloat FROM OroDataAuditBundle:AuditField afpb WHERE afpb.id = MAX(afb.id)) AS budgetAmount
SELECT
            )
            ->leftJoin('a.fields', 'afca', Join::WITH, 'afca.field = :closedAtField')
            ->leftJoin('a.fields', 'afc', Join::WITH, 'afc.field = :closeDateField')
            ->leftJoin('a.fields', 'afp', Join::WITH, 'afp.field = :probabilityField')
            ->leftJoin('a.fields', 'afb', Join::WITH, 'afb.field = :budgetAmountField')
            ->where('a.objectClass = :objectClass AND a.loggedAt < :moment')
            ->groupBy('a.objectId')
            ->having(
                <<<HAVING
NOT EXISTS(
    SELECT
        afcah.newDatetime
    FROM OroDataAuditBundle:AuditField afcah
    WHERE
        afcah.id = MAX(afca.id)
        AND afcah.newDatetime IS NOT NULL
)
AND EXISTS(
    SELECT
        afph.newFloat
    FROM OroDataAuditBundle:AuditField afph
    WHERE
        afph.id = MAX(afp.id)
)
HAVING
            )
            ->setParameters([
                'objectClass'           => 'Oro\Bundle\SalesBundle\Entity\Opportunity',
                'closedAtField'         => 'closedAt',
                'closeDateField'        => 'closeDate',
                'probabilityField'      => 'probability',
                'budgetAmountField'     => 'budgetAmount',
                'moment'                => $moment,
            ]);

        return $qb;
    }

    protected function addOwnersToDataAuditQB(QueryBuilder $qb, $ownerIds)
    {
        $qb
            ->join('a.fields', 'afo', Join::WITH, 'afo.field = :ownerField')
            ->andHaving(
                <<<HAVING
EXISTS(
    SELECT
        afoh.newText
    FROM OroDataAuditBundle:AuditField afoh
    WHERE
        afoh.id = MAX(afo.id)
        AND afoh.newText IN (SELECT afohu.username FROM OroUserBundle:User afohu WHERE afohu.id IN (:ownerIds))
)
HAVING
            )
            ->setParameter('ownerField', 'owner')
            ->setParameter('ownerIds', $ownerIds);
    }

    /**
     * @param array $result
     *
     * @return mixed
     */
    protected function getAggregatedResult(array $result)
    {
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
     * Processes data and ACL filters
     *
     * @param QueryBuilder    $queryBuilder
     * @param WidgetOptionBag $widgetOptions
     *
     * @return Query
     */
    protected function processDataQueryBuilder(QueryBuilder $queryBuilder, WidgetOptionBag $widgetOptions)
    {
        $this->widgetProviderFilter->filter($queryBuilder, $widgetOptions);

        return $this->aclHelper->apply($queryBuilder);
    }

    protected function applyQueryFilter(QueryBuilder $queryBuilder, WidgetOptionBag $widgetOptions)
    {
        $queryFilter = $widgetOptions->get('queryFilter', []);
        $filters     = isset($queryFilter['definition']['filters'])
            ? $queryFilter['definition']['filters']
            : [];
        if ($filters) {
            $this->filterProcessor
                ->process($queryBuilder, 'Oro\Bundle\SalesBundle\Entity\Opportunity', $filters, 'o');
        }
    }
}
