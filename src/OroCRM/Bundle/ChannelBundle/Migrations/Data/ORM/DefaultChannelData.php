<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use OroCRM\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class DefaultChannelData extends AbstractDefaultChannelDataFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $settingsProvider = $this->container->get('orocrm_channel.provider.settings_provider');

        $this->createChannelsForIntegrations($settingsProvider);
    }

    /**
     * @param SettingsProvider $settingsProvider
     */
    protected function createChannelsForIntegrations(SettingsProvider $settingsProvider)
    {
        // create channels for integrations
        $types        = $settingsProvider->getSourceIntegrationTypes();
        $integrations = $this->em->getRepository('OroIntegrationBundle:Channel')
            ->findBy(['type' => $types]);

        $lifetimeFields = [];
        $settings       = $settingsProvider->getLifetimeValueSettings();
        foreach ($settings as $singleChannelTypeData) {
            $lifetimeFields[$singleChannelTypeData['entity']] = $singleChannelTypeData['field'];
        }

        /** @var Integration $integration */
        foreach ($integrations as $integration) {
            $builder = $this->container->get('orocrm_channel.builder.factory')
                ->createBuilderForIntegration($integration);
            $builder->setOwner($integration->getOrganization());
            $builder->setDataSource($integration);
            $builder->setStatus($integration->getEnabled() ? Channel::STATUS_ACTIVE : Channel::STATUS_INACTIVE);
            $builder->setName($integration->getName() . ' channel');

            $channel = $builder->getChannel();
            $this->saveChannel($channel);

            foreach ($channel->getEntities() as $entity) {
                $this->fillChannelToEntity($channel, $entity, ['channel' => $integration]);
            }

            $this->updateLifetimeForAccounts($channel, $lifetimeFields);
        }
    }

    /**
     * @param Channel $channel
     */
    protected function saveChannel(Channel $channel)
    {
        $this->em->persist($channel);
        $this->em->flush();
    }

    /**
     * @param Channel  $channel
     * @param string[] $lifetimeFields
     */
    protected function updateLifetimeForAccounts(Channel $channel, array $lifetimeFields)
    {
        $persistedItemCount = 0;

        $accountRepo  = $this->em->getRepository('OroCRMAccountBundle:Account');
        $lifetimeRepo = $this->em->getRepository('OroCRMChannelBundle:LifetimeValueHistory');

        $accountRows = $accountRepo->createQueryBuilder('a')
            ->select('a.id')
            ->getQuery()
            ->getArrayResult();

        foreach ($accountRows as $accountRow) {
            $customerIdentity = $channel->getCustomerIdentity();
            if (isset($lifetimeFields[$customerIdentity])) {
                $account = $this->em->getReference($accountRepo->getClassName(), $accountRow['id']);
                /** @var LifetimeHistoryRepository $lifetimeRepo */
                $lifetimeAmount = $lifetimeRepo->calculateAccountLifetime(
                    $customerIdentity,
                    $lifetimeFields[$customerIdentity],
                    $account,
                    $channel
                );

                $history = new LifetimeValueHistory();
                $history->setAmount($lifetimeAmount);
                $history->setDataChannel($channel);
                $history->setAccount($account);
                $this->em->persist($history);
                $persistedItemCount++;
            }

            if ($persistedItemCount === self::BATCH_SIZE) {
                $this->em->flush();
                $this->em->clear($lifetimeRepo->getClassName());
                $persistedItemCount = 0;
            }
        }

        if ($persistedItemCount > 0) {
            $this->em->flush();
            $this->em->clear($lifetimeRepo->getClassName());
        }
    }
}
