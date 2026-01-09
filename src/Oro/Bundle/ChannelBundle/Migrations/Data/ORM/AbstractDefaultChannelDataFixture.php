<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\BatchBundle\ORM\Query\QueryCountCalculator;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Component\DependencyInjection\ContainerAwareInterface;
use Oro\Component\DependencyInjection\ContainerAwareTrait;

/**
 * The base class for fixtures that load default channels.
 * Provides logic for lifetime value updating and filling channel to entity.
 */
abstract class AbstractDefaultChannelDataFixture extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
{
    use ContainerAwareTrait;

    private const UPDATE_LIFETIME_READ_BATCH_SIZE = 1000;
    private const UPDATE_LIFETIME_WRITE_BATCH_SIZE = 200;

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadOrganizationAndBusinessUnitData::class];
    }

    protected function getRowCount(ObjectManager $manager, string $entityClass): int
    {
        return QueryCountCalculator::calculateCount(
            $manager->getRepository($entityClass)->createQueryBuilder('e')->getQuery()
        );
    }

    protected function fillChannelToEntity(
        ObjectManager $manager,
        Channel $channel,
        string $entityClass,
        array $additionalParameters = []
    ): void {
        $interfaces = class_implements($entityClass) ?: [];
        if (!\in_array(ChannelAwareInterface::class, $interfaces, true)) {
            return;
        }

        /** @var QueryBuilder $qb */
        $qb = $manager->createQueryBuilder()
            ->update($entityClass, 'e')
            ->set('e.dataChannel', $channel->getId())
            ->where('e.dataChannel IS NULL');
        if (!empty($additionalParameters)) {
            foreach ($additionalParameters as $parameterName => $value) {
                $qb
                    ->andWhere(sprintf('e.%s = :%s', $parameterName, $parameterName))
                    ->setParameter($parameterName, $value);
            }
        }
        $qb->getQuery()->execute();
    }

    /**
     * Returns map of lifetime fields per customer identity
     */
    protected function getLifetimeFieldsMap(): array
    {
        $lifetimeFields = [];
        $settings = $this->container->get('oro_channel.provider.settings_provider')->getLifetimeValueSettings();
        foreach ($settings as $singleChannelTypeData) {
            $lifetimeFields[$singleChannelTypeData['entity']] = $singleChannelTypeData['field'];
        }

        return $lifetimeFields;
    }

    protected function updateLifetimeForAccounts(ObjectManager $manager, Channel $channel): void
    {
        $lifetimeFields = $this->getLifetimeFieldsMap();

        $customerIdentity = $channel->getCustomerIdentity();
        if (!isset($lifetimeFields[$customerIdentity])) {
            return;
        }
        $lifetimeFieldName = $lifetimeFields[$customerIdentity];

        $accountIterator = new BufferedIdentityQueryResultIterator(
            $manager->getRepository(Account::class)->createQueryBuilder('a')->select('a.id')
        );
        $accountIterator->setBufferSize(self::UPDATE_LIFETIME_READ_BATCH_SIZE);

        $accountIds = [];
        foreach ($accountIterator as $accountRow) {
            $accountIds[] = $accountRow['id'];

            if (count($accountIds) === self::UPDATE_LIFETIME_WRITE_BATCH_SIZE) {
                $this->updateLifetime($manager, $accountIds, $channel, $customerIdentity, $lifetimeFieldName);
                $accountIds = [];
            }
        }

        if (count($accountIds) > 0) {
            $this->updateLifetime($manager, $accountIds, $channel, $customerIdentity, $lifetimeFieldName);
        }
    }

    protected function updateLifetime(
        ObjectManager $manager,
        array $accountIds,
        Channel $channel,
        string $customerIdentity,
        string $lifetimeFieldName
    ): void {
        $customerMetadata = $manager->getClassMetadata($customerIdentity);
        $lifetimeColumnName = $customerMetadata->getColumnName($lifetimeFieldName);

        $manager->getConnection()->executeStatement(
            'UPDATE orocrm_channel_lifetime_hist SET status = :status
             WHERE data_channel_id = :channel_id AND account_id IN (:account_ids)',
            ['status' => false, 'channel_id' => $channel->getId(), 'account_ids' => $accountIds],
            ['status' => Types::BOOLEAN, 'channel_id' => Types::INTEGER, 'account_ids' => Connection::PARAM_INT_ARRAY]
        );
        $manager->getConnection()->executeStatement(
            'INSERT INTO orocrm_channel_lifetime_hist'
            . ' (account_id, data_channel_id, status, amount, created_at)'
            . sprintf(
                ' SELECT e.account_id AS hist_account_id, e.data_channel_id AS hist_data_channel_id,'
                . ' e.account_id > 0 as hist_status, SUM(COALESCE(e.%s, 0)) AS hist_amount,'
                . ' TIMESTAMP :created_at AS hist_created_at',
                $lifetimeColumnName
            )
            . sprintf(' FROM %s AS e', $customerMetadata->getTableName())
            . ' WHERE e.data_channel_id = :channel_id AND e.account_id IN (:account_ids)'
            . ' GROUP BY hist_account_id, hist_data_channel_id, hist_status, hist_created_at',
            [
                'created_at' => new \DateTime(null, new \DateTimeZone('UTC')),
                'channel_id' => $channel->getId(),
                'account_ids' => $accountIds
            ],
            [
                'created_at' => Types::DATETIME_MUTABLE,
                'channel_id' => Types::INTEGER,
                'account_ids' => Connection::PARAM_INT_ARRAY
            ]
        );
    }
}
