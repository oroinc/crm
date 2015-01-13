<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

class LoadTrackingEventData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = [
        'tracking_event_ev1_1' => [
            'name' => 'ev1',
            'loggedAt' => '-3 days 10:01:11',
            'value' => 1,
            'userIdentifier' => 1,
            'url' => 'http://localhost',
            'reference' => '.ev1.3days.1'
        ],
        'tracking_event_ev1_2' => [
            'name' => 'ev1',
            'loggedAt' => '-3 days 10:01:12',
            'value' => 1,
            'userIdentifier' => 2,
            'url' => 'http://localhost',
            'reference' => '.ev1.3days.2'
        ],
        'tracking_event_ev2_1' => [
            'name' => 'ev2',
            'loggedAt' => '-3 days 10:01:11',
            'value' => 1,
            'userIdentifier' => 1,
            'url' => 'http://localhost',
            'reference' => '.ev2.3days.1'
        ],
        'tracking_event_ev1_3' => [
            'name' => 'ev1',
            'loggedAt' => '-2 days',
            'value' => 1,
            'userIdentifier' => 2,
            'url' => 'http://localhost',
            'reference' => '.ev1.2days.1'
        ],
        'tracking_event_ev1_4' => [
            'name' => 'ev1',
            'loggedAt' => '-1 day',
            'value' => 1,
            'userIdentifier' => 2,
            'url' => 'http://localhost',
            'reference' => '.ev1.1day.1'
        ],
        'tracking_event_ev1_5' => [
            'name' => 'ev1',
            'loggedAt' => 'now',
            'value' => 1,
            'userIdentifier' => 3,
            'url' => 'http://localhost',
            'reference' => '.ev1.now'
        ],
        'tracking_event_ev1_no campaign' => [
            'name' => 'ev1',
            'loggedAt' => '-3days 12:13:14',
            'value' => 1,
            'userIdentifier' => 3,
            'url' => 'http://localhost',
            'reference' => '.ev1.now',
            'code' => null
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $website = new TrackingWebsite();
        $website->setName('website1')
            ->setIdentifier(1)
            ->setUrl('http://localhost');
        $manager->persist($website);

        $campaignCodes = ['cmp1', 'cmp2', 'cmp3'];

        foreach ($campaignCodes as $code) {
            foreach ($this->data as $data) {
                $entity = new TrackingEvent();
                $entity->setWebsite($website)
                    ->setCode($code);

                $data['loggedAt'] = new \DateTime($data['loggedAt'], new \DateTimeZone('UTC'));

                $excludeProperties = ['reference'];
                $propertyAccessor = PropertyAccess::createPropertyAccessor();
                foreach ($data as $property => $value) {
                    if (in_array($property, $excludeProperties)) {
                        continue;
                    }
                    $propertyAccessor->setValue($entity, $property, $value);
                }

                $this->setReference($code . $data['reference'], $entity);
                $manager->persist($entity);
            }
        }
        $manager->flush();
    }
}
