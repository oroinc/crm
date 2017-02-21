<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;

abstract class B2bBigNumberProvider
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var WidgetProviderFilterManager */
    protected $widgetProviderFilter;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /**
     * @param RegistryInterface $doctrine
     * @param WidgetProviderFilterManager $widgetProviderFilter
     * @param BigNumberDateHelper $dateHelper
     */
    public function __construct(
        RegistryInterface $doctrine,
        WidgetProviderFilterManager $widgetProviderFilter,
        BigNumberDateHelper $dateHelper
    ) {
        $this->doctrine             = $doctrine;
        $this->widgetProviderFilter = $widgetProviderFilter;
        $this->dateHelper           = $dateHelper;
    }
}
