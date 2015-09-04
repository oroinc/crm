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
}
