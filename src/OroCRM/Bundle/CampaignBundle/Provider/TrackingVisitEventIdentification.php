<?php

namespace OroCRM\Bundle\CampaignBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Provider\TrackingEventIdentifierInterface;

class TrackingVisitEventIdentification implements TrackingEventIdentifierInterface
{
    /** @var ObjectManager */
    protected $em;

    /**
     * @param Registry  $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(TrackingVisit $trackingVisit)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function identify(TrackingVisit $trackingVisit)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityTarget()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventTargets()
    {
        return [
            'OroCRM\Bundle\CampaignBundle\Entity\Campaign'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableVisitEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        $code = $trackingVisitEvent->getWebEvent()->getCode();
        return !is_null($code);
    }

    /**
     * {@inheritdoc}
     */
    public function processEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        $code = $trackingVisitEvent->getWebEvent()->getCode();
        $campaign = $this->em->getRepository('OroCRMCampaignBundle:Campaign')->findOneBy(['code' => $code]);
        if ($campaign) {
            return [$campaign];
        }

        return [];
    }
}
