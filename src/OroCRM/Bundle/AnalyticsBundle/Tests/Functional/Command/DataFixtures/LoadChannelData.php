<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\Command\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class LoadChannelData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    protected $data = [
        'not_supports' => [
            'customerIdentity' => 'OroCRM\Bundle\ChannelBundle\Entity\CustomerIdentity',
            'name' => 'CustomerIdentityChannel',
            'channelType' => 'b2b',
            'status' => Channel::STATUS_ACTIVE,
            'reference' => 'Channel.CustomerIdentity'
        ],
        'supports' => [
            'customerIdentity' => 'OroCRM\Bundle\MagentoBundle\Entity\Customer',
            'name' => 'CustomerChannel',
            'channelType' => 'magento',
            'status' => Channel::STATUS_ACTIVE,
            'reference' => 'Channel.CustomerChannel'
        ],
        'notActive' => [
            'customerIdentity' => 'OroCRM\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface',
            'name' => 'AnalyticsAwareInterfaceChannel',
            'channelType' => 'magento',
            'status' => Channel::STATUS_INACTIVE,
            'reference' => 'Channel.AnalyticsAwareInterface'
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [__NAMESPACE__ . '\LoadEntitiesData'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            $entity = new Channel();

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
