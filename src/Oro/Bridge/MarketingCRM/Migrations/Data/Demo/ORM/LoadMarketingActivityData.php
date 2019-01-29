<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadMarketingActivityData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    const OPENS_COUNT = 5;
    const CLICKS_COUNT = 10;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadCampaignEmailData',
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var Organization $organization */
        $organization = $this->getReference('default_organization');
        /** @var array $entities */
        $entities = $manager->getRepository('OroContactBundle:Contact')->findAll();
        /** @var EmailCampaign $emailCampaign */
        $emailCampaign = $this->getReference('marketing_activity_campaign');
        $rareTypes = [
            MarketingActivity::TYPE_HARD_BOUNCE,
            MarketingActivity::TYPE_SOFT_BOUNCE,
            MarketingActivity::TYPE_UNSUBSCRIBE
        ];

        $timezoneUTC = new \DateTimeZone('UTC');


        $hours = mt_rand(12, 24);
        $sendDate = date_create('-' . $hours . 'hours', $timezoneUTC);
        foreach ($entities as $entity) {
            //add send activity
            $this->addMarketingActivity(
                $manager,
                $emailCampaign,
                $entity,
                $organization,
                MarketingActivity::TYPE_SEND,
                $sendDate
            );
            /**
             * randomly add click/open (for the most entities) or bounce activities.
             */
            $randomPath = mt_rand(0, 100);
            if ($randomPath > 15) {
                $opensCount = mt_rand(1, self::OPENS_COUNT);
                for ($j = 0; $j < $opensCount; $j++) {
                    $this->addMarketingActivity(
                        $manager,
                        $emailCampaign,
                        $entity,
                        $organization,
                        MarketingActivity::TYPE_OPEN,
                        date_create('-' . (mt_rand($hours - 6, $hours)) . 'hours', $timezoneUTC)
                    );
                }
                //clicks should be later than opens.
                $clicksCount = mt_rand(1, self::CLICKS_COUNT);
                for ($j = 0; $j < $clicksCount; $j++) {
                    $this->addMarketingActivity(
                        $manager,
                        $emailCampaign,
                        $entity,
                        $organization,
                        MarketingActivity::TYPE_CLICK,
                        date_create('-' . mt_rand(0, $hours - 6) . 'hours', $timezoneUTC),
                        "http://example.com/test{$j}.html"
                    );
                }
            } else {
                $this->addMarketingActivity(
                    $manager,
                    $emailCampaign,
                    $entity,
                    $organization,
                    $rareTypes[mt_rand(0, count($rareTypes) - 1)],
                    date_create('-' . mt_rand(0, $hours) . 'hours', $timezoneUTC)
                );
            }
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param EmailCampaign $emailCampaign
     * @param object $entity
     * @param Organization $organization
     * @param string $type
     * @param \DateTime $date
     * @param string $details
     */
    protected function addMarketingActivity(
        $manager,
        $emailCampaign,
        $entity,
        $organization,
        $type,
        $date,
        $details = null
    ) {
        $activityFactory = $this->container->get('oro_marketing_activity.factory');
        $timezoneUTC = new \DateTimeZone('UTC');
        $marketingActivity = $activityFactory->create(
            $emailCampaign->getCampaign(),
            ClassUtils::getClass($entity),
            $entity->getId(),
            $date,
            $type,
            $organization,
            $emailCampaign->getId()
        );
        if ($details) {
            $marketingActivity->setDetails($details);
        }
        $manager->persist($marketingActivity);
    }
}
