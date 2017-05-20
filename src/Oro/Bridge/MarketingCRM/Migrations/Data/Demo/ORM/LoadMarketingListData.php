<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;

class LoadMarketingListData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadSegmentsData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $defaultUser = $manager->getRepository('OroUserBundle:User')->findOneBy([] , ['id' => 'ASC']);
        $type = $manager->getRepository('OroMarketingListBundle:MarketingListType')->findOneBy(['name' => 'dynamic']);
        $segment = $manager->getRepository('OroSegmentBundle:Segment')->findOneBy(['name' => 'Contact List Segment']);
        $list = new MarketingList();
        $list->setName('Contact list')
            ->setDescription('Contact list')
            ->setEntity('Oro\Bundle\ContactBundle\Entity\Contact')
            ->setOrganization($this->getReference('default_organization'))
            ->setOwner($defaultUser)
            ->setType($type)
            ->setSegment($segment)
            ->setCreatedAt(date_create('-' . (mt_rand(3600, 32535)) . 'seconds', new \DateTimeZone('UTC')))
            ->setUpdatedAt($list->getCreatedAt());

        $manager->persist($list);
        $manager->flush();
    }
}
