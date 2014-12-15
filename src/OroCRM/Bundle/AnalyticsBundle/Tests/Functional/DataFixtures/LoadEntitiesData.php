<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class LoadEntitiesData extends AbstractFixture implements DependentFixtureInterface
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
        return [__NAMESPACE__ . '\LoadChannelData'];
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
