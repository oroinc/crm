<?php

namespace Oro\Bridge\MarketingCRM\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Bundle\MagentoBundle\Provider\TrackingCustomerIdentificationEvents as TCI;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Provider\TrackingEventIdentifierInterface;

/**
 * Checks if given tracking event is supported by identifier
 */
class TrackingCustomerIdentification implements TrackingEventIdentifierInterface
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var  ConfigProvider */
    protected $extendConfigProvider;

    /** @var  SettingsProvider */
    protected $settingsProvider;

    /**
     * @param ManagerRegistry  $registry
     * @param ConfigProvider   $extendConfigProvider
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(
        ManagerRegistry $registry,
        ConfigProvider $extendConfigProvider,
        SettingsProvider $settingsProvider
    ) {
        $this->registry             = $registry;
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

                /**
                 * Remove dependency on exact magento channel type in CRM-8153
                 */
                if ($type && $type === MagentoChannelType::TYPE) {
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
            $target  = $this->registry->getManagerForClass($this->getIdentityTarget())
                ->getRepository($this->getIdentityTarget())
                ->findOneBy(
                    [
                        'originId'    => $userIdentifier,
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
        /**
         * Remove dependency on exact magento channel type in CRM-8153
         */
        return $this->settingsProvider->getCustomerIdentityFromConfig(MagentoChannelType::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventTargets()
    {
        return [
            'Oro\Bundle\MagentoBundle\Entity\Order',
            'Oro\Bundle\MagentoBundle\Entity\Customer',
            'Oro\Bundle\MagentoBundle\Entity\Product',
            'Oro\Bundle\MagentoBundle\Entity\Cart'
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

                /**
                 * Remove dependency on exact magento channel type in CRM-8153
                 */
                if ($type && $type === MagentoChannelType::TYPE) {
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

        $channel    = $trackingVisitEvent->getVisit()->getTrackingWebsite()->getChannel();
        $eventName  = $trackingVisitEvent->getEvent()->getName();
        $eventValue = $trackingVisitEvent->getWebEvent()->getValue();

        switch ($eventName) {
            case TCI::EVENT_CART_ITEM_ADDED:
                $targets[] = $this->registry->getManagerForClass('OroMagentoBundle:Product')
                    ->getRepository('OroMagentoBundle:Product')
                    ->findOneBy(
                        [
                            'originId' => (int)$eventValue
                        ]
                    );
                break;
            case TCI::EVENT_ORDER_PLACE_SUCCESS:
                $targets[] = $this->registry->getManagerForClass('OroMagentoBundle:Order')
                    ->getRepository('OroMagentoBundle:Order')
                    ->findOneBy(
                        [
                            'subtotalAmount' => $eventValue,
                            'dataChannel'    => $channel
                        ]
                    );
                break;
            case TCI::EVENT_ORDER_PLACED:
                $targets[] = $this->registry->getManagerForClass('OroMagentoBundle:Order')
                    ->getRepository('OroMagentoBundle:Order')
                    ->findOneBy(
                        [
                            'incrementId' => $eventValue,
                            'dataChannel' => $channel
                        ]
                    );
                break;
            case TCI::EVENT_CHECKOUT_STARTED:
                $targets[] = $this->registry->getManagerForClass('OroMagentoBundle:Cart')
                    ->getRepository('OroMagentoBundle:Cart')
                    ->findOneBy(
                        [
                            'subTotal'    => $eventValue,
                            'dataChannel' => $channel
                        ]
                    );
                break;
            case TCI::EVENT_CUSTOMER_LOGOUT:
                $targets[] = $this->registry->getManagerForClass('OroMagentoBundle:Customer')
                    ->getRepository('OroMagentoBundle:Customer')
                    ->findOneBy(
                        [
                            'originId'    => (int)$eventValue,
                            'dataChannel' => $channel
                        ]
                    );
                break;
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
