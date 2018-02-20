<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\EntityBundle\ORM\DatabasePlatformInterface;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

class UpdateIntegrationEntitiesData extends AbstractFixture implements
    VersionedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->updateOrderAddress($manager);
        $this->updateOrderItems($manager);
        $this->updateCartItems($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    protected function updateOrderAddress(ObjectManager $manager)
    {
        $mySqlQuery = <<<QUERY
UPDATE orocrm_magento_order AS magento_order, orocrm_magento_order_address AS magento_order_address
SET magento_order_address.channel_id = magento_order.channel_id
WHERE magento_order_address.owner_id = magento_order.id;
QUERY;

        $postgreSqlQuery = <<<QUERY
UPDATE orocrm_magento_order_address channel_id
SET channel_id = magento_order.channel_id
FROM orocrm_magento_order magento_order;
QUERY;

        $this->runQuery($mySqlQuery, $postgreSqlQuery, $manager);
    }

    /**
     * @param ObjectManager $manager
     */
    protected function updateOrderItems(ObjectManager $manager)
    {
        $mySqlQuery = <<<QUERY
UPDATE orocrm_magento_order AS magento_order, orocrm_magento_order_items AS magento_order_items
SET magento_order_items.channel_id = magento_order.channel_id
WHERE magento_order_items.order_id = magento_order.id;
QUERY;

        $postgreSqlQuery = <<<QUERY
UPDATE orocrm_magento_order_items channel_id
SET channel_id = magento_order.channel_id
FROM orocrm_magento_order magento_order;
QUERY;

        $this->runQuery($mySqlQuery, $postgreSqlQuery, $manager);
    }

    /**
     * @param ObjectManager $manager
     */
    protected function updateCartItems(ObjectManager $manager)
    {
        $mySqlQuery = <<<QUERY
UPDATE orocrm_magento_cart AS magento_cart, orocrm_magento_cart_item AS magento_cart_item
SET magento_cart_item.channel_id = magento_cart.channel_id
WHERE magento_cart_item.cart_id = magento_cart.id;
QUERY;

        $postgreSqlQuery = <<<QUERY
UPDATE orocrm_magento_cart_item channel_id
SET channel_id = magento_cart.channel_id
FROM orocrm_magento_cart magento_cart;
QUERY;

        $this->runQuery($mySqlQuery, $postgreSqlQuery, $manager);
    }

    /**
     * @param string $mySqlQuery
     * @param string $postgreSqlQuery
     * @param EntityManagerInterface|ObjectManager $manager
     */
    protected function runQuery($mySqlQuery, $postgreSqlQuery, EntityManagerInterface $manager)
    {
        /** @var Connection $conn */
        $conn = $manager->getConnection();

        $platformName = $conn->getDatabasePlatform()->getName();

        $query = null;

        if ($platformName === DatabasePlatformInterface::DATABASE_MYSQL) {
            $query = $mySqlQuery;
        } elseif ($platformName === DatabasePlatformInterface::DATABASE_POSTGRESQL) {
            $query = $postgreSqlQuery;
        } else {
            return;
        }

        try {
            $conn->beginTransaction();
            $conn->exec($query);
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollBack();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '1.1';
    }
}
