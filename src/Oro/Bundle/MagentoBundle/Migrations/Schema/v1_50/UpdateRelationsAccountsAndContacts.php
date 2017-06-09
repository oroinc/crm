<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_50;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateRelationsAccountsAndContacts implements Migration, ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // send migration message to queue. we should process this migration asynchronous because instances
        // could have a lot of magento customers in system.
        $this->container->get('oro_message_queue.message_producer')
            ->send(UpdateRelationsAccountsAndContactsProcessor::TOPIC_NAME, '');
    }
}
