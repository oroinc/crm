<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class LoadChannelData extends AbstractFixture
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
            'data' => [RFMAwareInterface::RFM_STATE_KEY => true],
            'reference' => 'Channel.CustomerChannel'
        ],
        'second supported' => [
            'customerIdentity' => 'OroCRM\Bundle\MagentoBundle\Entity\Customer',
            'name' => 'CustomerChannel2',
            'channelType' => 'magento',
            'status' => Channel::STATUS_ACTIVE,
            'data' => [RFMAwareInterface::RFM_STATE_KEY => true],
            'reference' => 'Channel.CustomerChannel2'
        ],
        'rfm disabled' => [
            'customerIdentity' => 'OroCRM\Bundle\MagentoBundle\Entity\Customer',
            'name' => 'CustomerChannel3',
            'channelType' => 'magento',
            'status' => Channel::STATUS_ACTIVE,
            'data' => [RFMAwareInterface::RFM_STATE_KEY => false],
            'reference' => 'Channel.CustomerChannel3'
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
