<?php

namespace Oro\Bundle\ChannelBundle\Entity\Repository;

use Carbon\Carbon;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Security\Core\Util\ClassUtils;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueAverageAggregation;

class LifetimeValueAverageAggregationRepository extends EntityRepository
{
    const BATCH_SIZE = 50;

    /** @var array */
    protected $itemsToWrite = [];

    /**
     * Run per channel aggregation
     * If $initialAggregation option set to false then run aggregation only current month or run from scratch otherwise
     *
     * @param string $timeZone
     * @param bool   $initialAggregation
     */
    public function aggregate($timeZone, $initialAggregation = false)
    {
        $em       = $this->getEntityManager();
        $now      = new \DateTime('now', new \DateTimeZone($timeZone));
        $channels = $em->getRepository('OroChannelBundle:Channel')->findAll();

        /** @var Channel $channel */
        foreach ($channels as $channel) {
            if ($initialAggregation) {
                /*
                 * Convert creation date from UTC to local timezone, this needed for correct start period calculation
                 * For example: channel created at 2014-05-01 05:00:00 UTC local timezone is USA/LA
                 *              so first entry should be saved for 04-2014
                 * Also reset day and time to 1 day of month in order to ensure that interval always will
                 * include current month
                 */
                $startDate = Carbon::instance($channel->getCreatedAt());
                $startDate->setTimezone(new \DateTimeZone($timeZone));
                $startDate->firstOfMonth();

                $period = new \DatePeriod($startDate, new \DateInterval('P1M'), $now);
                /** @var \DateTime $date */
                foreach ($period as $date) {
                    $this->doMonthAggregation($channel, $date);
                }
            } else {
                $this->doMonthAggregation($channel, $now, true);
            }
        }

        $this->ensureRealized();
    }

    /**
     * @param bool $useDelete
     */
    public function clearTableData($useDelete = false)
    {
        $table = $this->getClassMetadata()->getTableName();

        if ($useDelete) {
            // clear table using DELETE statement might be useful when there is no permissions for truncate
            // another point for test purposes in order to do not break transaction
            $this->getEntityManager()
                ->createQueryBuilder()
                ->delete($this->getEntityName(), 'lva')
                ->getQuery()
                ->execute();
        } else {
            $connection = $this->getEntityManager()->getConnection();
            $platform   = $connection->getDatabasePlatform();
            $connection->executeUpdate($platform->getTruncateTableSQL($table, true));
        }
    }

    /**
     * @param \DateTime                      $startDate
     * @param string|\DateInterval|\DateTime $endDateParam - Could be passed exact date, date period object or string
     * @param array|null                     $channelIds   - Channel ids to filter or null if filtration is no needed
     *
     * @return array
     */
    public function findForPeriod(\DateTime $startDate, $endDateParam = 'P1Y', $channelIds = null)
    {
        if (!$endDateParam instanceof \DateTime) {
            $endDate = clone $startDate;
            $endDate->add($endDate instanceof \DateInterval ? $endDate : new \DateInterval($endDateParam));
        } else {
            $endDate = $endDateParam;
        }

        /** @var QueryBuilder */
        $qb = $this->createQueryBuilder('lva');
        $qb->select('IDENTITY(lva.dataChannel) as channelId');
        $qb->addSelect('lva.amount');
        $qb->addSelect('lva.month');
        $qb->addSelect('lva.year');
        $qb->andWhere($qb->expr()->between('lva.aggregationDate', ':dateStart', ':dateEnd'));
        $qb->addGroupBy('lva.dataChannel', 'lva.year', 'lva.month', 'lva.amount');
        $qb->setParameter('dateStart', $startDate);
        $qb->setParameter('dateEnd', $endDate);

        if (null !== $channelIds) {
            $qb->andWhere($qb->expr()->in('lva.dataChannel', ':channelIds'))
                ->setParameter('channelIds', $channelIds);
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param Channel   $channel
     * @param \DateTime $date
     * @param bool      $lookUpForExistingEntity
     *
     * @return LifetimeValueAverageAggregation
     */
    private function doMonthAggregation(Channel $channel, \DateTime $date, $lookUpForExistingEntity = false)
    {
        $entity           = null;
        $channelId        = $channel->getId();
        $channelClassName = ClassUtils::getRealClass($channel);
        if ($lookUpForExistingEntity) {
            $entity = $this->findOneBy(
                [
                    'dataChannel' => $channelId,
                    'month'       => $date->format('m'),
                    'year'        => $date->format('Y')
                ]
            );
        }

        $entity = $entity ?: new LifetimeValueAverageAggregation();
        $entity->setAggregationDate($date);
        $entity->setDataChannel($this->getEntityManager()->getReference($channelClassName, $channelId));
        $entity->setAmount($this->getAggregatedValue($channel, $date));
        $this->write($entity);
    }

    /**
     * @param Channel   $channel
     * @param \DateTime $date Datetime object in system timezone
     *
     * @return float
     */
    private function getAggregatedValue(Channel $channel, \DateTime $date)
    {
        $sql = <<<SQL
  SELECT AVG(h.{amount})
  FROM {tableName} h
  JOIN(
    SELECT MAX(h1.{id}) as identity
    FROM {tableName} h1
    WHERE h1.{dataChannel} = :channelId AND h1.{createdAt} <= :endDate
    GROUP BY h1.{account}
  ) maxres ON maxres.identity = h.{id}
SQL;

        $sqlNames = $this->getSQLColumnNamesArray();
        $sql      = preg_replace_callback(
            '/{(\w+)}/',
            function ($matches) use ($sqlNames) {
                $fieldName = trim(end($matches));
                if (isset($sqlNames[$fieldName])) {
                    return $sqlNames[$fieldName];
                }

                throw new \RuntimeException(sprintf('Entity does not have field named "%s"', $fieldName));
            },
            $sql
        );

        $calculationPeriodEnd = Carbon::instance($date);
        $calculationPeriodEnd->firstOfMonth();
        $calculationPeriodEnd->addMonth();

        return $this->getEntityManager()
            ->getConnection()
            ->executeQuery(
                $sql,
                ['channelId' => $channel->getId(), 'endDate' => $calculationPeriodEnd],
                ['channelId' => Type::INTEGER, 'endDate' => Type::DATETIME]
            )
            ->fetchColumn(0);
    }

    /**
     * @return array
     */
    private function getSQLColumnNamesArray()
    {
        $em       = $this->getEntityManager();
        $metadata = $em->getClassMetadata('OroChannelBundle:LifetimeValueHistory');

        $sqlNames = ['tableName' => $metadata->getTableName()];
        foreach ($metadata->getFieldNames() as $fieldName) {
            $sqlNames[$fieldName] = $metadata->getColumnName($fieldName);
        }
        foreach ($metadata->getAssociationNames() as $fieldName) {
            $sqlNames[$fieldName] = $metadata->getSingleAssociationJoinColumnName($fieldName);
        }

        return $sqlNames;
    }

    /**
     * Ensure that all buffered items wrote to DB
     */
    private function ensureRealized()
    {
        if (!empty($this->itemsToWrite)) {
            $this->write([], true);
        }
    }

    /**
     * @param array|object $items
     * @param bool         $enforceFlush
     */
    private function write($items, $enforceFlush = false)
    {
        $items              = is_array($items) ? $items : [$items];
        $this->itemsToWrite = array_merge($this->itemsToWrite, $items);

        if (count($this->itemsToWrite) >= self::BATCH_SIZE || $enforceFlush) {
            $em = $this->getEntityManager();
            foreach ($this->itemsToWrite as $item) {
                $em->persist($item);
            }

            $em->flush();
            $em->clear();
            $this->itemsToWrite = [];
        }
    }
}
