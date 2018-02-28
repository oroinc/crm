<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LoadChannelsData extends AbstractFixture
{
    const ENABLED_CART_CHANNEL = 'enabled cart channel';
    const DISABLED_CART_CHANNEL = 'disabled cart channel';

    /**
     * @return  array
     */
    private function getChannelData()
    {
        return [
            self::ENABLED_CART_CHANNEL => [
                'name' => 'enabled cart channel',
                'status' => true
            ],
            self::DISABLED_CART_CHANNEL => [
                'name' => 'enabled cart channel',
                'status' => false
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getChannelData() as $referenceName => $channelsData) {
            $channel = new Channel();
            $channel
                ->setName($channelsData['name'])
                ->setStatus($channelsData['status'])
                ->setOwner($this->loadOwner($manager))
                ->setChannelType('testType')
                ->setCreatedAt(new \DateTime('now'))
                ->setUpdatedAt(new \DateTime('now'))
                ->setCustomerIdentity('test1');

            $manager->persist($channel);
            $this->setReference($referenceName, $channel);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @return \Extend\Entity\EX_OroOrganizationBundle_Organization|null|Organization
     */
    protected function loadOwner(ObjectManager $manager)
    {
        return $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
    }
}
