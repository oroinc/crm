<?php

namespace Oro\Bundle\TrackingBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;

use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

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
        $userIdentifier = $this->parse($trackingVisit->getUserIdentifier());
        if ($userIdentifier) {
            $result = [
                'parsedUID'    => $userIdentifier,
                'targetObject' => null
            ];

            $target = $this->em->getRepository($this->getTarget())->findOneBy(['originId' => $userIdentifier]);
            if ($target) {
                $result['targetObject'] = $target;
            }

            return $result;
        }

        return null;
    }

    /**
     * Returns FQCN for which visit should be assigned to.
     *
     * @return null|string
     */
    public function getTarget()
    {
        return $this->settingsProvider->getCustomerIdentityFromConfig(ChannelType::TYPE);
    }

    /**
     * Parse user identifier string and returns PK value by which identity object can be retrived.
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
                    $data                     = explode('=', $string);
                    $identifierData[$data[0]] = $data[1];
                }
            );

            if (array_key_exists('id', $identifierData) && $identifierData['id'] !== 'guest') {
                return $identifierData['id'];
            }
        }

        return null;
    }
}
