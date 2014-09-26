<?php

namespace OroCRM\Bundle\ChannelBundle\Entity\Repository;

use Carbon\Carbon;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Security\Core\Util\ClassUtils;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueAverageAggregation;

class LifetimeValueAverageAggregationRepository extends EntityRepository
{
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
        $channels = $em->getRepository('OroCRMChannelBundle:Channel')->findAll();

        /** @var Channel $channel */
        foreach ($channels as $channel) {
            if ($initialAggregation) {
                $startDate = $channel->getCreatedAt();
                $period    = new \DatePeriod($startDate, new \DateInterval('P1M'), $now);
                /** @var \DateTime $date */
                foreach ($period as $date) {
                    $date->setTimezone(new \DateTimeZone($timeZone));
                    $entry = $this->doMonthAggregation($channel, $date);
                    $em->persist($entry);
                }
            } else {
                $entry = $this->doMonthAggregation($channel, $now, true);
                $em->persist($entry);
            }
        }

        $em->flush();
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
     * @param \DateTime $date
     *
     * @return array
     */
    public function findAmountStatisticsByDate($date)
    {
        /** @var QueryBuilder */
        $qb = $this->createQueryBuilder('dl');

        $qb->select(
            '(dl.dataChannel) as dataChannel, dl.aggregationDate as createdAt, dl.month as month, dl.year as year'
        );
        $qb->addSelect($qb->expr()->max('dl.amount') . ' as amount');
        $qb->andWhere('dl.aggregationDate > :date');
        $qb->addGroupBy('dl.year', 'dl.month');
        $qb->setParameter('date', $date);

        return $qb->getQuery()->getResult();
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

        $aggregationDate = Carbon::createFromTimestampUTC($date->format('U'));
        $aggregationDate->firstOfMonth();

        $entity = $entity ?: new LifetimeValueAverageAggregation();
        $entity->setAggregationDate($aggregationDate);
        $entity->setDataChannel($this->getEntityManager()->getReference($channelClassName, $channelId));
        $entity->setAmount($this->getAggregatedValue($channel, $date));

        return $entity;
    }

    /**
     * @param Channel   $channel
     * @param \DateTime $date Datetime object in system timezone
     *
     * @return array
     */
    private function getAggregatedValue(Channel $channel, \DateTime $date)
    {
        $em       = $this->getEntityManager();
        $metadata = $em->getClassMetadata('OroCRMChannelBundle:LifetimeValueHistory');

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

        $sqlNames = ['tableName' => $metadata->getTableName()];
        foreach ($metadata->getFieldNames() as $fieldName) {
            $sqlNames[$fieldName] = $metadata->getColumnName($fieldName);
        }
        foreach ($metadata->getAssociationNames() as $fieldName) {
            $sqlNames[$fieldName] = $metadata->getSingleAssociationJoinColumnName($fieldName);
        }

        $sql = preg_replace_callback(
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

        return $em
            ->getConnection()
            ->executeQuery(
                $sql,
                ['channelId' => $channel->getId(), 'endDate' => $calculationPeriodEnd],
                ['channelId' => Type::INTEGER, 'endDate' => Type::DATETIME]
            )
            ->fetchColumn(0);
    }
}
