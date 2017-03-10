<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;

abstract class B2bBigNumberProvider
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var WidgetProviderFilterManager */
    protected $widgetProviderFilter;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /**
     * @param RegistryInterface           $doctrine
     * @param AclHelper                   $aclHelper
     * @param WidgetProviderFilterManager $widgetProviderFilter
     * @param BigNumberDateHelper         $dateHelper
     */
    public function __construct(
        RegistryInterface $doctrine,
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
