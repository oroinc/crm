<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class LoadNewsletterSubscriberStatusData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = [
        NewsletterSubscriber::STATUS_SUBSCRIBED => 'Subscribed',
        NewsletterSubscriber::STATUS_UNSUBSCRIBED => 'Unsubscribed',
        NewsletterSubscriber::STATUS_UNCONFIRMED => 'Unconfirmed',
        NewsletterSubscriber::STATUS_NOT_ACTIVE => 'Not active'
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName('mage_subscr_status');

        /** @var EnumValueRepository $enumValueRepository */
        $enumValueRepository = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->data as $id => $name) {
            $enumOption = $enumValueRepository->createEnumValue($name, $priority++, false, $id);
            $manager->persist($enumOption);
        }
        $manager->flush();
    }
}
