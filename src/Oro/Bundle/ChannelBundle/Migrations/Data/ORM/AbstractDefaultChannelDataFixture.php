<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Data\ORM;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\BatchBundle\ORM\Query\QueryCountCalculator;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\ChannelBundle\Entity\Channel;

abstract class AbstractDefaultChannelDataFixture extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
{
    const UPDATE_LIFETIME_READ_BATCH_SIZE = 1000;
    const UPDATE_LIFETIME_WRITE_BATCH_SIZE = 200;

    /** @var ContainerInterface */
    protected $container;

    /** @var EntityManager */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em        = $container->get('doctrine')->getManager();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData'];
    }

    /**
     * @param string $entity
     *
     * @return int
     */
    protected function getRowCount($entity)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->em->getRepository($entity)
            ->createQueryBuilder('e');

        return QueryCountCalculator::calculateCount($qb->getQuery());
    }

    /**
     * @param Channel $channel
     * @param string  $entity
     * @param array   $additionalParameters
     */
    protected function fillChannelToEntity(Channel $channel, $entity, $additionalParameters = [])
    {
        $interfaces = class_implements($entity) ?: [];
        if (!in_array('Oro\\Bundle\\ChannelBundle\\Model\\ChannelAwareInterface', $interfaces)) {
            return;
        }

        /** @var QueryBuilder $qb */
        $qb = $this->em->createQueryBuilder()
            ->update($entity, 'e')
            ->set('e.dataChannel', $channel->getId())
            ->where('e.dataChannel IS NULL');
        if (!empty($additionalParameters)) {
            foreach ($additionalParameters as $parameterName => $value) {
                $qb->andWhere(
                    sprintf(
                        'e.%s = :%s',
                        $parameterName,
                        $parameterName
                    )
                )->setParameter($parameterName, $value);
            }
        }
        $qb->getQuery()
            ->execute();
    }

    /**
     * Returns map of lifetime fields per customer identity
     *
     * @return array
     */
    protected function getLifetimeFieldsMap()
    {
        $settingsProvider = $this->container->get('oro_channel.provider.settings_provider');

        $lifetimeFields = [];
        $settings       = $settingsProvider->getLifetimeValueSettings();
        foreach ($settings as $singleChannelTypeData) {
            $lifetimeFields[$singleChannelTypeData['entity']] = $singleChannelTypeData['field'];
        }

        return $lifetimeFields;
    }

    /**
     * @param Channel $channel
     */
    protected function updateLifetimeForAccounts(Channel $channel)
    {
        $lifetimeFields = $this->getLifetimeFieldsMap();

        $customerIdentity = $channel->getCustomerIdentity();
        if (!isset($lifetimeFields[$customerIdentity])) {
            return;
        }
        $lifetimeFieldName = $lifetimeFields[$customerIdentity];
        $accountRepo       = $this->em->getRepository('OroAccountBundle:Account');

        $accountIterator = new BufferedIdentityQueryResultIterator(
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
            'UPDATE orocrm_channel_lifetime_hist SET status = :status
             WHERE data_channel_id = :channel_id AND account_id IN (:account_ids)',
            ['status' => false, 'channel_id' => $channel->getId(), 'account_ids' => $accountIds],
            ['status' => Type::BOOLEAN, 'channel_id' => Type::INTEGER, 'account_ids' => Connection::PARAM_INT_ARRAY]
        );
        $this->em->getConnection()->executeUpdate(
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
                'created_at' => Type::DATETIME,
                'channel_id' => Type::INTEGER,
                'account_ids' => Connection::PARAM_INT_ARRAY
            ]
        );
    }
}
