<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityBundle\Exception\InvalidEntityException;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class OrderRepository extends EntityRepository
{
    /**
     * @param Cart|Customer $item
     * @param string        $field
     *
     * @return Cart|Customer|null $item
     * @throws InvalidEntityException
     */
    public function getLastPlacedOrderBy($item, $field)
    {
        if (!($item instanceof Cart) && !($item instanceof Customer)) {
            throw new InvalidEntityException();
        }
        $qb = $this->createQueryBuilder('o');
        $qb->where('o.' . $field . ' = :item');
        $qb->setParameter('item', $item);
        $qb->orderBy('o.updatedAt', 'DESC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get customer orders subtotal amount
     *
     * @param Customer $customer
     * @return string
     */
    public function getCustomerOrdersSubtotalAmount(Customer $customer)
    {
        $qb = $this->createQueryBuilder('o')
            ->select('sum(o.subtotalAmount) as subtotal')
            ->where('o.customer = :customer')->setParameter('customer', $customer)
            ->andWhere('o.status != :status')->setParameter('status', 'canceled');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @return array
     */
    public function getAverageOrderAmount(AclHelper $aclHelper)
    {
        /** @var \DateTime $sliceDate */
        list($sliceDate, $monthMatch, $channelTemplate) = $this->getOrderSliceDateAndTemplates();

        // get all channels
        /** @var EntityManager $entityManager */
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->getRepository('OroCRMChannelBundle:Channel')->createQueryBuilder('c');
        $queryBuilder->select('c.id, c.name')->orderBy('c.name');
        $channels = $aclHelper->apply($queryBuilder)->execute();

        // prepare result template
        $result = [];
        foreach ($channels as $channel) {
            $channelId = $channel['id'];
            $channelName = $channel['name'];
            $result[$channelId] = ['name' => $channelName, 'data' => $channelTemplate];
        }

        // execute data query
        $queryBuilder = $this->createQueryBuilder('o');
        $selectClause = '
            IDENTITY(o.dataChannel) AS dataChannelId,
            MONTH(o.createdAt) as monthCreated,
            AVG(o.subtotalAmount - o.discountAmount) as averageOrderAmount';
        $queryBuilder->select($selectClause)
            ->where('o.createdAt > :sliceDate')->setParameter('sliceDate', $sliceDate)
            ->groupBy('dataChannelId, monthCreated');
        $amountStatistics = $aclHelper->apply($queryBuilder)->execute();

        foreach ($amountStatistics as $row) {
            $channelId   = (int)$row['dataChannelId'];
            $month       = (int)$row['monthCreated'];
            $year        = $monthMatch[$month]['year'];
            $orderAmount = (float)$row['averageOrderAmount'];

            if (isset($result[$channelId]['data'][$year][$month])) {
                $result[$channelId]['data'][$year][$month] += $orderAmount;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getOrderSliceDateAndTemplates()
    {
        // calculate slice date
        $currentYear  = (int)date('Y');
        $currentMonth = (int)date('m');

        $sliceYear  = $currentMonth == 12 ? $currentYear : $currentYear - 1;
        $sliceMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
        $sliceDate  = new \DateTime(sprintf('%s-%s-01', $sliceYear, $sliceMonth), new \DateTimeZone('UTC'));

        // calculate match for month and default channel template
        $monthMatch = [];
        $channelTemplate = [];
        if ($sliceYear != $currentYear) {
            for ($i = $sliceMonth; $i <= 12; $i++) {
                $monthMatch[$i] = ['year' => $sliceYear, 'month' => $i];
                $channelTemplate[$sliceYear][$i] = 0;
            }
        }
        for ($i = 1; $i <= $currentMonth; $i++) {
            $monthMatch[$i] = ['year' => $currentYear, 'month' => $i];
            $channelTemplate[$currentYear][$i] = 0;
        }

        return [$sliceDate, $monthMatch, $channelTemplate];
    }
}
