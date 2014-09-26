<?php

namespace OroCRM\Bundle\ChannelBundle\Provider\Lifetime;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AverageLifetimeWidgetProvider
{
    /** @var RegistryInterface */
    protected $registry;

    /** @var LocaleSettings */
    protected $localeSettings;

    /**
     * @param RegistryInterface $registry
     * @param LocaleSettings    $localeSettings
     */
    public function __construct(RegistryInterface $registry, LocaleSettings $localeSettings)
    {
        $this->registry       = $registry;
        $this->localeSettings = $localeSettings;
    }

    public function getChartView()
    {
        $now      = new \DateTime('now', new \DateTimeZone($this->localeSettings->getTimeZone()));
        $interval = new \DateInterval('P1M');
        $endDate  = clone $now;
        $endDate->add($interval);

        $period = new \DatePeriod($now, new \DateInterval('P1M'), $endDate);

    }
}
