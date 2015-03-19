<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;

use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Provider\TrackingEventIdentifierInterface;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class TrackingCustomerIdentification implements TrackingEventIdentifierInterface
{
    /** @var ObjectManager */
    protected $em;

    /** @var  ConfigProvider */
    protected $extendConfigProvider;

    /** @var  SettingsProvider */
    protected $settingsProvider;

    /**
     * @param Registry         $doctrine
     * @param ConfigProvider   $extendConfigProvider
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(
        Registry $doctrine,
        ConfigProvider $extendConfigProvider,
        SettingsProvider $settingsProvider
    ) {
        $this->em                   = $doctrine->getManager();
        $this->extendConfigProvider = $extendConfigProvider;
        $this->settingsProvider     = $settingsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(TrackingVisit $trackingVisit)
    {
        $hasTrackingWebsiteChannel = $this->extendConfigProvider->hasConfig(
            'Oro\Bundle\TrackingBundle\Entity\TrackingWebsite',
            'channel'
        );

        if ($hasTrackingWebsiteChannel) {
            $trackingWebsite = $trackingVisit->getTrackingWebsite();
            if (method_exists($trackingWebsite, 'getChannel')) {
                /** @var Channel $channel */
                $channel = $trackingWebsite->getChannel();
                $type    = $channel ? $channel->getChannelType() : false;

                if ($type && $type === ChannelType::TYPE) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function identify(TrackingVisit $trackingVisit)
    {
        $userIdentifier = $trackingVisit->getParsedUID() > 0
            ? $trackingVisit->getParsedUID()
            : $this->parse($trackingVisit->getUserIdentifier());
        if ($userIdentifier) {
            $result = [
                'parsedUID'    => $userIdentifier,
                'targetObject' => null
            ];

            $channel = $trackingVisit->getTrackingWebsite()->getChannel();
            $target = $this->em->getRepository($this->getIdentityTarget())->findOneBy(
                [
                    'originId'  => $userIdentifier,
                    'dataChannel' => $channel
                ]
            );
            if ($target) {
                $result['targetObject'] = $target;
            }

            return $result;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityTarget()
    {
        return $this->settingsProvider->getCustomerIdentityFromConfig(ChannelType::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventTargets()
    {
        return [
            'OroCRM\Bundle\MagentoBundle\Entity\Order',
            'OroCRM\Bundle\MagentoBundle\Entity\Customer',
            'OroCRM\Bundle\MagentoBundle\Entity\Product',
            'OroCRM\Bundle\MagentoBundle\Entity\Cart'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableVisitEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        $hasTrackingWebsiteChannel = $this->extendConfigProvider->hasConfig(
            'Oro\Bundle\TrackingBundle\Entity\TrackingWebsite',
            'channel'
        );

        if ($hasTrackingWebsiteChannel) {
            $trackingWebsite = $trackingVisitEvent->getVisit()->getTrackingWebsite();
            if (method_exists($trackingWebsite, 'getChannel')) {
                /** @var Channel $channel */
                $channel = $trackingWebsite->getChannel();
                $type    = $channel ? $channel->getChannelType() : false;

                if ($type && $type === ChannelType::TYPE) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function processEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        $targets = [];

        // identifier
        $targets[] = $trackingVisitEvent->getVisit()->getIdentifierTarget();

        $channel = $trackingVisitEvent->getVisit()->getTrackingWebsite()->getChannel();

        // process 'cart item added' event
        if ($trackingVisitEvent->getEvent()->getName() === 'cart item added') {
            $targets[] = $this->em->getRepository('OroCRMMagentoBundle:Product')->findOneBy(
                [
                    'originId' => (int)$trackingVisitEvent->getWebEvent()->getValue()
                ]
            );
        }

        // process 'order successfully placed' event
        if ($trackingVisitEvent->getEvent()->getName() === 'order successfully placed') {
            $targets[] = $this->em->getRepository('OroCRMMagentoBundle:Order')->findOneBy(
                [
                    'subtotalAmount' => $trackingVisitEvent->getWebEvent()->getValue(),
                    'dataChannel' => $channel
                ]
            );
        }

        // process 'user entered checkout' event
        if ($trackingVisitEvent->getEvent()->getName() === 'user entered checkout') {
            $targets[] = $this->em->getRepository('OroCRMMagentoBundle:Cart')->findOneBy(
                [
                    'subTotal'   => $trackingVisitEvent->getWebEvent()->getValue(),
                    'dataChannel' => $channel
                ]
            );
        }

        return $targets;
    }

    /**
     * Parse user identifier string and returns value for column by which identity object can be retrieved.
     * Returns null in case identifier is not found.
     *
     * @param string $identifier
     *
     * @return string|null
     */
    protected function parse($identifier = null)
    {
        if (!empty($identifier)) {
            $identifierArray = explode('; ', $identifier);
            $identifierData  = [];
            array_walk(
                $identifierArray,
                function ($string) use (&$identifierData) {
                    $data = explode('=', $string);
                    if (count($data) === 2) {
                        $identifierData[$data[0]] = $data[1];
                    }
                }
            );

            if (array_key_exists('id', $identifierData) && $identifierData['id'] !== 'guest') {
                return $identifierData['id'];
            }
        }

        return null;
    }
}
