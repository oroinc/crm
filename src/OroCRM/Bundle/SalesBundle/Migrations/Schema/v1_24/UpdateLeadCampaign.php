<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_24;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;

class UpdateLeadCampaign implements
    Migration,
    ExtendExtensionAwareInterface,
    ContainerAwareInterface
{
    /**
     * @var ExtendExtension
     */
    private $extendExtension;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

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
        /** @var ExtendOptionsManager $extendOptionsManager */
        $extendOptionsManager = $this->container->get('oro_entity_extend.migration.options_manager');

        $extendOptionsManager->setColumnOptions(
            'orocrm_sales_lead',
            'campaign',
            ['extend' => ['owner' => ExtendScope::OWNER_SYSTEM]]
        );
    }
}
