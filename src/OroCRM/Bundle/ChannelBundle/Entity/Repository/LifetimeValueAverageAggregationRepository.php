<?php

namespace OroCRM\Bundle\ChannelBundle\Entity\Repository;

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

        $qb->select('(dl.dataChannel) as dataChannel, dl.createdAt as createdAt, dl.month as month, dl.year as year');
        $qb->addSelect($qb->expr()->max('dl.amount') . ' as amount');
        $qb->andWhere('dl.createdAt > :date');
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

        $entity = $entity ?: new LifetimeValueAverageAggregation();
        $entity->setAggregationDate($date);
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
  SELECT AVG(h.{{ amount }})
  FROM {{ tableName }} h
  JOIN(
    SELECT MAX(h1.{{ id }}) as identity
    FROM {{ tablName }}
    WHERE h1.{{ dataChannel }} = :channelId AND h1.{{ aggregationDate }} BETWEEN :startDate AND :endDate
    GROUP BY h1.{{ account }}
  ) maxres ON maxres.identity = h.{{ id }}
SQL;

        $sqlNames = ['tableName' => $metadata->getTableName()];
        $fields   = $metadata->getFieldNames();
        foreach ($fields as $fieldName) {
            $sqlNames[$fieldName] = $metadata->getColumnName($fieldName);
        }

        preg_replace_callback(
            '/{{(.+)}}/',
            function ($matches) use ($sqlNames) {
                $fieldName = trim(end($matches));
                if (isset($sqlNames[$fieldName])) {
                    return $sqlNames[$fieldName];
                }

                throw new \RuntimeException(sprintf('Entity does not have field named "%s"', $fieldName));
            },
            $sql
        );

        $endDate = clone $date;
        $endDate->add(new \DateInterval('P1M'));

        return $em
            ->getConnection()
            ->executeQuery(
                $sql,
                ['channelId' => $channel->getId(), 'startDate' => $date, 'endDate' => $endDate],
                ['channelId' => Type::INTEGER, 'startDate' => Type::DATETIME, 'endDate' => Type::DATETIME]
            )
            ->fetchColumn(0);
    }
}
