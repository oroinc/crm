<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\InternalTransportSettings;

class LoadCampaignEmailData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadMarketingListData',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->getClassMetadata('Oro\Bundle\CampaignBundle\Entity\EmailCampaign')->setLifecycleCallbacks([]);

        $marketingLists = $manager->getRepository('OroMarketingListBundle:MarketingList')->findAll();
        $defaultUser = $manager->getRepository('OroUserBundle:User')->findOneBy(['username' => 'admin']);
        $marketingListsMax = count($marketingLists) - 1;
        $emailCampaign     = new EmailCampaign();
        $transportSettings = new InternalTransportSettings();
        $emailCampaign->setTransportSettings($transportSettings)
            ->setOwner($defaultUser)
            ->setOrganization($this->getReference('default_organization'))
            ->setMarketingList($marketingLists[mt_rand(0, $marketingListsMax)])
            ->setName('Contact list campaign')
            ->setSent(1)
            ->setTransport('mailchimp')
            ->setSchedule('manual')
            ->setSenderEmail('magento.shop@magento-oro.com')
            ->setSenderName('Magento Shop')
            ->setCreatedAt(date_create('-' . (mt_rand(3600, 32535)) . 'seconds', new \DateTimeZone('UTC')));
        $emailCampaign->setUpdatedAt($emailCampaign->getCreatedAt());
        $emailCampaign->setSentAt($emailCampaign->getUpdatedAt());

        $manager->persist($emailCampaign);
        $manager->flush();
    }
}
