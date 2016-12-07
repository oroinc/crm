<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class LoadCustomerData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    protected $data = [
        [
            'reference' => 'Channel.CustomerIdentity.CustomerIdentity',
            'channel' => 'Channel.CustomerChannel',
            'firstName' => 'Customer1',
            'recency' => 1,
            'frequency' => 1,
            'monetary' => 1,
        ],
        [
            'reference' => 'Channel.CustomerChannel.Customer',
            'channel' => 'Channel.CustomerChannel',
            'firstName' => 'Customer2',
            'recency' => 2,
            'frequency' => 2,
            'monetary' => 2,
        ],
        [
            'reference' => 'Channel.CustomerChannel.Customer2',
            'channel' => 'Channel.CustomerChannel2',
            'firstName' => 'Customer3',
            'recency' => 3,
            'frequency' => 3,
            'monetary' => 3,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadChannelData::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            /** @var Channel $channel */
            $channel = $this->getReference($data['channel']);

            $now = new \DateTime();
            $entity = new Customer();
            $entity
                ->setCreatedAt($now)
                ->setUpdatedAt($now)
                ->setDataChannel($channel);

            $excludeProperties = ['reference', 'channel'];
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
