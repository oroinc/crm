<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class LoadNewsletterSubscriberStatusData extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

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

        /** @var ConfigProvider $configProvider */
        $configProvider = $this->container->get('oro_entity_config.provider.importexport');
        $configManager = $configProvider->getConfigManager();

        $id = $configProvider->getConfig($className, 'id');
        $id->set('identity', true);
        $name = $configProvider->getConfig($className, 'name');
        $name->remove('identity');

        $configManager->persist($id);
        $configManager->persist($name);
        $configManager->flush();

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
