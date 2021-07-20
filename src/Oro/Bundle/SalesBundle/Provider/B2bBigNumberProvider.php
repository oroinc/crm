<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

abstract class B2bBigNumberProvider
{
    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var WidgetProviderFilterManager */
    protected $widgetProviderFilter;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    public function __construct(
        ManagerRegistry $doctrine,
        AclHelper $aclHelper,
        WidgetProviderFilterManager $widgetProviderFilter,
        BigNumberDateHelper $dateHelper
    ) {
        $this->doctrine             = $doctrine;
        $this->aclHelper            = $aclHelper;
        $this->widgetProviderFilter = $widgetProviderFilter;
        $this->dateHelper           = $dateHelper;
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
}
