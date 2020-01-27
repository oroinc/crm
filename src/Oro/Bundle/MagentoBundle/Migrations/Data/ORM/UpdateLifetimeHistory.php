<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fixes sales lifetime value history if it does not match with the account's sales lifetime value.
 */
class UpdateLifetimeHistory extends AbstractFixture implements ContainerAwareInterface
{
    const MAX_UPDATE_CHUNK_SIZE = 50;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager|EntityManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $settingsProvider = $this->container->get('oro_channel.provider.settings_provider');
        $lifetimeSettings = $settingsProvider->getLifetimeValueSettings();
        if (!array_key_exists(MagentoChannelType::TYPE, $lifetimeSettings)) {
            return;
        }
        $magentoChannelSettings = $lifetimeSettings[MagentoChannelType::TYPE];
        $customerIdentityClass = $magentoChannelSettings['entity'];
        $lifetimeField = $magentoChannelSettings['field'];

        $accountClass = Account::class;
        $channelClass = Channel::class;

        /** @var LifetimeHistoryRepository $lifetimeRepo */
        $lifetimeRepo = $manager->getRepository('OroChannelBundle:LifetimeValueHistory');

        $brokenAccountQb = $this->getBrokenAccountsQueryBuilder($customerIdentityClass, $lifetimeField, $lifetimeRepo);
        $brokenAccountsData = new BufferedQueryResultIterator($brokenAccountQb);

        $customerIdentities = [];
        foreach ($lifetimeSettings as $singleChannelTypeData) {
            $customerIdentities[$singleChannelTypeData['entity']] = $singleChannelTypeData['field'];
        }

        $toOutDate = [];
        foreach ($brokenAccountsData as $brokenDataRow) {
            /** @var Account $account */
            $account = $manager->getReference($accountClass, $brokenDataRow['account_id']);
            /** @var Channel $channel */
            $channel = $manager->getReference($channelClass, $brokenDataRow['channel_id']);
            $lifetimeAmount = $lifetimeRepo
                ->calculateAccountLifetime($customerIdentities, $account, $channel);

            $history = new LifetimeValueHistory();
            $history->setAmount($lifetimeAmount);
            $history->setDataChannel($channel);
            $history->setAccount($account);

            $manager->persist($history);

            $toOutDate[] = [$account, $channel, $history];
        }
        $manager->flush();

        foreach (array_chunk($toOutDate, self::MAX_UPDATE_CHUNK_SIZE) as $chunks) {
            $lifetimeRepo->massStatusUpdate($chunks);
        }
    }

    /**
     * @param string $customerIdentityClass
     * @param string $lifetimeField
     * @param EntityRepository $historyRepository
     * @return QueryBuilder
     */
    protected function getBrokenAccountsQueryBuilder(
        $customerIdentityClass,
        $lifetimeField,
        EntityRepository $historyRepository
    ) {
        return $historyRepository->createQueryBuilder('h')
            ->select('IDENTITY(c.account) as account_id', 'IDENTITY(c.dataChannel) as channel_id')
            ->innerJoin(
                $customerIdentityClass,
                'c',
                Join::WITH,
                'h.account = c.account AND c.dataChannel = h.dataChannel'
            )
            ->where('h.status = :status')
            ->setParameter('status', true)
            ->groupBy('c.account, c.dataChannel')
            ->having(sprintf('SUM(c.%s) != SUM(h.amount)', $lifetimeField));
    }
}
