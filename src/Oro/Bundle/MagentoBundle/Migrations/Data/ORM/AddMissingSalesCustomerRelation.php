<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DatabasePlatformInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;
use Oro\Bundle\SalesBundle\Entity\Customer as SalesCustomer;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddMissingSalesCustomerRelation extends AbstractFixture implements
    ContainerAwareInterface,
    VersionedFixtureInterface
{
    const BUFFER_SIZE = 1000;

    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
     * @param EntityManagerInterface $manager
     */
    public function load(ObjectManager $manager)
    {
        if (!$this->container->hasParameter('installed') || !$this->container->getParameter('installed')) {
            return;
        }

        $associationName = ExtendHelper::buildAssociationName(
            Customer::class,
            CustomerScope::ASSOCIATION_KIND
        );

        $platformName = $manager->getConnection()->getDatabasePlatform()->getName();
        if ($platformName === DatabasePlatformInterface::DATABASE_POSTGRESQL) {
            $updateQuery = sprintf(
                'UPDATE %s AS ca SET %s_id = mc.id FROM %s AS mc WHERE ca.account_id = mc.account_id ',
                $manager->getClassMetadata(SalesCustomer::class)->getTableName(),
                $associationName,
                $manager->getClassMetadata(Customer::class)->getTableName()
            );
        } elseif ($platformName === DatabasePlatformInterface::DATABASE_MYSQL) {
            $updateQuery = sprintf(
                'UPDATE %s ca JOIN %s mc ON ca.account_id = mc.account_id SET ca.%s_id = mc.id',
                $manager->getClassMetadata(SalesCustomer::class)->getTableName(),
                $manager->getClassMetadata(Customer::class)->getTableName(),
                $associationName
            );
        } else {
            return;
        }

        $connection = $manager->getConnection();
        $connection->transactional(function () use ($connection, $updateQuery) {
            $connection->executeUpdate($updateQuery);
        });

        $qb = $manager->getRepository(Customer::class)->createQueryBuilder('mc');
        $qb->select(['mc.id', 'IDENTITY(mc.account) AS account_id']);
        $qb->leftJoin(
            SalesCustomer::class,
            'ca',
            Join::WITH,
            sprintf('ca.%s = mc.id', $associationName)
        );
        $qb->where($qb->expr()->isNull(sprintf('ca.%s', $associationName)));
        $qb->andWhere($qb->expr()->isNotNull('mc.account'));

        $iterator = new BufferedIdentityQueryResultIterator($qb->getQuery());
        $iterator->setBufferSize(self::BUFFER_SIZE);
        $connection->transactional(function () use ($manager, $iterator, $associationName) {
            $connection = $manager->getConnection();
            $insertQB = $connection->createQueryBuilder();
            $tableName = $manager->getClassMetadata(SalesCustomer::class)->getTableName();

            foreach ($iterator as $item) {
                $insertQuery = $insertQB
                    ->insert($tableName)
                    ->values([
                        'account_id' => $item['account_id'],
                        $associationName . '_id' => $item['id'],
                    ]);

                $connection->executeQuery($insertQuery);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '1.1';
    }
}
