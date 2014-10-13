<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class DefaultChannelData extends AbstractDefaultChannelDataFixture
{
    const UPDATE_LIFETIME_READ_BATCH_SIZE  = 1000;
    const UPDATE_LIFETIME_WRITE_BATCH_SIZE = 200;

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
        $customerIdentity = $channel->getCustomerIdentity();
        if (!isset($lifetimeFields[$customerIdentity])) {
            return;
        }
        $lifetimeFieldName = $lifetimeFields[$customerIdentity];

        $accountRepo  = $this->em->getRepository('OroCRMAccountBundle:Account');

        $accountIterator = new BufferedQueryResultIterator(
            $accountRepo->createQueryBuilder('a')->select('a.id')
        );
        $accountIterator->setBufferSize(self::UPDATE_LIFETIME_READ_BATCH_SIZE);

        $accountIds = [];
        foreach ($accountIterator as $accountRow) {
            $accountIds[] = $accountRow['id'];

            if (count($accountIds) === self::UPDATE_LIFETIME_WRITE_BATCH_SIZE) {
                $this->updateLifetime($accountIds, $channel, $customerIdentity, $lifetimeFieldName);
                $accountIds = [];
            }
        }

        if (count($accountIds) > 0) {
            $this->updateLifetime($accountIds, $channel, $customerIdentity, $lifetimeFieldName);
        }
    }

    /**
     * @param int[]   $accountIds
     * @param Channel $channel
     * @param string  $customerIdentity
     * @param string  $lifetimeFieldName
     */
    protected function updateLifetime(array $accountIds, Channel $channel, $customerIdentity, $lifetimeFieldName)
    {
        $customerMetadata   = $this->em->getClassMetadata($customerIdentity);
        $lifetimeColumnName = $customerMetadata->getColumnName($lifetimeFieldName);

        $this->em->getConnection()->executeUpdate(
            'UPDATE orocrm_channel_lifetime_hist SET status = :status'
            . sprintf(
                ' WHERE data_channel_id = %d AND account_id IN (%s)',
                $channel->getId(),
                implode(',', $accountIds)
            ),
            ['status' => false],
            ['status' => 'boolean']
        );
        $this->em->getConnection()->executeUpdate(
            'INSERT INTO orocrm_channel_lifetime_hist'
            . ' (account_id, data_channel_id, status, amount, created_at)'
            . sprintf(
                ' SELECT e.account_id AS hist_account_id, e.data_channel_id AS hist_data_channel_id,'
                . ' :status as hist_status, SUM(COALESCE(e.%s, 0)) AS hist_amount,'
                . ' :created_at AS hist_created_at',
                $lifetimeColumnName
            )
            . sprintf(' FROM %s AS e', $customerMetadata->getTableName())
            . sprintf(
                ' WHERE e.data_channel_id = %d AND e.account_id IN (%s)',
                $channel->getId(),
                implode(',', $accountIds)
            )
            . ' GROUP BY hist_account_id, hist_data_channel_id, hist_status, hist_created_at',
            ['status' => true, 'created_at' => new \DateTime(null, new \DateTimeZone('UTC'))],
            ['status' => Type::BOOLEAN, 'created_at' => Type::DATETIME]
        );
    }
}
