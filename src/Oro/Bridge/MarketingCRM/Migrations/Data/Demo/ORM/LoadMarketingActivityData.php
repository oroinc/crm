<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;

class LoadMarketingActivityData extends AbstractFixture implements DependentFixtureInterface
{
    const MARKETING_ACTIVITIES_COUNT = 2500;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadCampaignData',
            'Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadCampaignEmailData',
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var Organization[] $organization */
        $organization = $this->getReference('default_organization');
        /** @var Lead[] $leads */
        $leads = $manager->getRepository('OroSalesBundle:Lead')->findAll();
        $contacts = $manager->getRepository('OroContactBundle:Contact')->findAll();
        $entities = array_merge($leads, $contacts);
        /** @var Campaign[] $campaigns */
        $campaigns = $manager->getRepository('OroCampaignBundle:Campaign')->findAll();
        /** @var EmailCampaign[] $emailCampaigns */
        $emailCampaigns = $manager->getRepository('OroCampaignBundle:EmailCampaign')->findAll();
        $enumClass = ExtendHelper::buildEnumValueClassName('ma_type');
        /** @var array $types */
        $types = $manager->getRepository($enumClass)->findAll();

        $timezoneUTC = new \DateTimeZone('UTC');
        $campaignsMax = count($campaigns) - 1;
        $typesMax = count($types) - 1;
        $entitiesMax = count($entities) - 1;
        $emailCampaignsMax = count($emailCampaigns) - 1;
        $emailCampaign = $emailCampaigns[mt_rand(0, $emailCampaignsMax)];

        for ($i = 0; $i < self::MARKETING_ACTIVITIES_COUNT; $i++) {
            $marketingActivity = new MarketingActivity();
            $entity = $entities[mt_rand(0, $entitiesMax)];
            $marketingActivity->setOwner($organization)
                ->setDetails('')
                ->setActionDate(date_create('-' . (mt_rand(0, 32535)) . 'seconds', $timezoneUTC))
                ->setCampaign($campaigns[mt_rand(0, $campaignsMax)])
                ->setEntityClass(get_class($entity))
                ->setEntityId($entity->getId())
                ->setRelatedCampaignClass(EmailCampaign::class)
                ->setRelatedCampaignId($emailCampaign->getId())
                ->setType($types[mt_rand(0, $typesMax)]);

            $manager->persist($marketingActivity);
        }

        $manager->flush();
    }
}
