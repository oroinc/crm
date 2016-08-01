<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingEventDictionary;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;
use OroCRM\Bundle\MagentoBundle\Provider\TrackingCustomerIdentification as TCI;

class LoadTrackingWebsiteData extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
{
    use ContainerAwareTrait;

    /**
     * Number of TrackingVisits to generate per customer
     *
     * @var int
     */
    const VISITS_PER_CUSTOMER = 10;

    /**
     * Number of events per visit
     * Total events would be customers x VISITS_PER_CUSTOMER x EVENTS_PER_VISIT
     *
     * @var int
     */
    const EVENTS_PER_VISIT = 20;

    /**
     * The time window that events are generated in days
     *
     * @var int
     */
    const EVENTS_PERIOD = 75;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadMagentoData'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $om)
    {
        $organization = $this->getReference('default_organization');
        $customers = $om->getRepository('OroCRMMagentoBundle:Customer')->findAll();

        $websites = $this->persistTrackingWebsites($om, $organization);
        $eventDictionary = $this->persistEventDictionaries($om, $websites);

        foreach ($customers as $customer) {
            $website = $websites[$customer->getDataChannel()->getId()];
            $websiteEvents = $eventDictionary[$website->getIdentifier()];

            for ($i = 0; $i < static::VISITS_PER_CUSTOMER; $i++) {
                $visit = $this->persistTrackingVisit($om, $website, $customer);

                for ($j = 0; $j < static::EVENTS_PER_VISIT; $j++) {
                    $this->persistTrackingVisitEvent($om, $visit, $websiteEvents);
                }
            }
        }

        $om->flush();
    }

    /**
     * @param ObjectManager $om
     * @param Organization $organization
     * @return TrackingWebsite[]
     */
    protected function persistTrackingWebsites(ObjectManager $om, Organization $organization)
    {
        $channels = $om->getRepository('OroCRMChannelBundle:Channel')->findBy([
            'channelType' => ChannelType::TYPE
        ]);

        $websites = [];
        foreach ($channels as $channel) {
            $website = new TrackingWebsite();
            $website->setName($channel->getName())
                ->setIdentifier(uniqid())
                ->setUrl('http://magento.domain')
                ->setChannel($channel)
                ->setOrganization($organization);

            $om->persist($website);
            $websites[$channel->getId()] = $website;
        }

        return $websites;
    }

    /**
     * @param ObjectManager $om
     * @param array $websites
     *
     * @return TrackingEventDictionary[]
     */
    protected function persistEventDictionaries(ObjectManager $om, array $websites)
    {
        $events = [
            TCI::EVENT_REGISTRATION_FINISHED,
            TCI::EVENT_CART_ITEM_ADDED,
            TCI::EVENT_CHECKOUT_STARTED,
            TCI::EVENT_ORDER_PLACE_SUCCESS,
            TCI::EVENT_ORDER_PLACED,
            TCI::EVENT_CUSTOMER_LOGIN,
            TCI::EVENT_CUSTOMER_LOGOUT,
            TCI::EVENT_VISIT,
        ];

        $dictionaries = [];
        foreach ($websites as $website) {
            foreach ($events as $eventName) {
                $event = new TrackingEventDictionary();
                $event->setName($eventName);
                $event->setWebsite($website);

                $om->persist($event);
                $dictionaries[$website->getIdentifier()][] = $event;
            }
        }

        return $dictionaries;
    }

    /**
     * @param ObjectManager $om
     * @param TrackingWebsite $website
     * @param Customer $customer
     *
     * @return TrackingVisit
     */
    protected function persistTrackingVisit(
        ObjectManager $om,
        TrackingWebsite $website,
        Customer $customer
    ) {
        $randomDays = mt_rand(0, static::EVENTS_PERIOD - 1);
        $start = new \DateTime(sprintf('-%s day', $randomDays));

        $end = new \DateTime();
        $end->setTimestamp(mt_rand($start->getTimestamp(), time()));

        $visit = new TrackingVisit();
        $visit->setTrackingWebsite($website)
            ->setVisitorUid(uniqid())
            ->setUserIdentifier('id=' . $customer->getOriginId())
            ->setIdentifierTarget($customer)
            ->setFirstActionTime($start)
            ->setLastActionTime($end);

        $om->persist($visit);

        return $visit;
    }

    /**
     * @param ObjectManager $om
     * @param TrackingVisit $visit
     * @param TrackingEventDictionary[] $eventDictionary
     */
    protected function persistTrackingVisitEvent(ObjectManager $om, TrackingVisit $visit, array $eventDictionary)
    {
        $event = $eventDictionary[array_rand($eventDictionary)];

        $eventTimestamp = mt_rand(
            $visit->getFirstActionTime()->getTimestamp(),
            $visit->getLastActionTime()->getTimestamp()
        );

        $eventDate = new \DateTime();
        $eventDate->setTimestamp($eventTimestamp);

        $trackingEvent = new TrackingEvent();
        $trackingEvent->setWebsite($visit->getTrackingWebsite())
            ->setName($event->getName())
            ->setValue(1)
            ->setUserIdentifier($visit->getUserIdentifier())
            ->setUrl('http://magento.domain')
            ->setTitle('Magento Store')
            ->setLoggedAt($eventDate)
        ;

        $om->persist($trackingEvent);

        $visitEvent = new TrackingVisitEvent();
        $visitEvent->setVisit($visit)
            ->setEvent($event)
            ->setWebEvent($trackingEvent)
            ->setWebsite($visit->getTrackingWebsite())
            ->addAssociationTarget($visit->getIdentifierTarget())
        ;

        $om->persist($visitEvent);
    }
}
