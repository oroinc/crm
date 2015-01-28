<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use OroCRM\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

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
        $settingsProvider = $this->container->get('orocrm_channel.provider.settings_provider');
        $lifetimeSettings = $settingsProvider->getLifetimeValueSettings();
        if (!array_key_exists(ChannelType::TYPE, $lifetimeSettings)) {
            return;
        }
        $magentoChannelSettings = $lifetimeSettings[ChannelType::TYPE];
        $customerIdentityClass = $magentoChannelSettings['entity'];
        $lifetimeField = $magentoChannelSettings['field'];

        $accountClass = $this->container->getParameter('orocrm_account.account.entity.class');
        $channelClass = $this->container->getParameter('orocrm_channel.entity.class');

        /** @var LifetimeHistoryRepository $lifetimeRepo */
        $lifetimeRepo = $manager->getRepository('OroCRMChannelBundle:LifetimeValueHistory');

        $brokenAccountQb = $this->getBrokenAccountsQueryBuilder($customerIdentityClass, $lifetimeField, $lifetimeRepo);
        $brokenAccountsData = new BufferedQueryResultIterator($brokenAccountQb);

        $toOutDate = [];
        foreach ($brokenAccountsData as $brokenDataRow) {
            /** @var Account $account */
            $account = $manager->getReference($accountClass, $brokenDataRow['account_id']);
            /** @var Channel $channel */
            $channel = $manager->getReference($channelClass, $brokenDataRow['channel_id']);
            $lifetimeAmount = $lifetimeRepo
                ->calculateAccountLifetime($customerIdentityClass, $lifetimeField, $account, $channel);

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
