<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Helper;

use DateTime;
use DateTimeZone;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class BigNumberDateHelper
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /**
     * @param RegistryInterface $doctrine
     * @param AclHelper $aclHelper
     */
    public function __construct(RegistryInterface $doctrine, AclHelper $aclHelper)
    {
        $this->doctrine = $doctrine;
        $this->aclHelper = $aclHelper;
    }

    /**
     * @param array  $dateRange
     * @param string $entity
     * @param string $field
     *
     * @return DateTime[]
     */
    public function getPeriod($dateRange, $entity, $field)
    {
        $start = $dateRange['start'];
        $end   = $dateRange['end'];

        if ($dateRange['type'] === AbstractDateFilterType::TYPE_LESS_THAN) {
            $qb    = $this->doctrine
                ->getRepository($entity)
                ->createQueryBuilder('e')
                ->select(sprintf('MIN(e.%s) as val', $field));
            $start = $this->aclHelper->apply($qb)->getSingleScalarResult();
            $start = new DateTime($start, new DateTimeZone('UTC'));
        }

        return [$start, $end];
    }

    /**
     * @param integer $weeksDiff
     *
     * @return DateTime[]
     */
    public function getLastWeekPeriod($weeksDiff = 0)
    {
        $end = new DateTime('last Saturday', new DateTimeZone('UTC'));
        $end->setTime(23, 59, 59);

        $start = clone $end;
        $start->modify('-6 days');
        $start->setTime(0, 0, 0);

        if ($weeksDiff) {
            $days = $weeksDiff * 7;
            $start->modify("{$days} days");
            $end->modify("{$days} days");
        }

        return [
            'start' => $start,
            'end'   => $end
        ];
    }
}
