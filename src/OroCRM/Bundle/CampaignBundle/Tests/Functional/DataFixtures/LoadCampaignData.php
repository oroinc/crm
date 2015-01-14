<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\CampaignBundle\Entity\Campaign;

class LoadCampaignData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = [
        'campaign1' => [
            'name' => 'Campaign1',
            'code' => 'cmp1',
            'reportPeriod' => Campaign::PERIOD_DAILY,
            'reference' => 'Campaign.Campaign1'
        ],
        'campaign2' => [
            'name' => 'Campaign2',
            'code' => 'cmp2',
            'reportPeriod' => Campaign::PERIOD_HOURLY,
            'reportRefreshDate' => '-1 day',
            'reference' => 'Campaign.Campaign2'
        ],
        'campaign3' => [
            'name' => 'Campaign3',
            'code' => 'cmp3',
            'reportPeriod' => Campaign::PERIOD_MONTHLY,
            'reportRefreshDate' => '-2 day',
            'reference' => 'Campaign.Campaign2'
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            $entity = new Campaign();
            if (isset($data['reportRefreshDate'])) {
                $data['reportRefreshDate'] = new \DateTime($data['reportRefreshDate'], new \DateTimeZone('UTC'));
            }

            $excludeProperties = ['reference'];
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            foreach ($data as $property => $value) {
                if (in_array($property, $excludeProperties)) {
                    continue;
                }
                $propertyAccessor->setValue($entity, $property, $value);
            }

            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
