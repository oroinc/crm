<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_20;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RemoveExtendSourceField implements Migration, ContainerAwareInterface
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
        $queries->addQuery(new RemoveExtendSourceFieldQuery());

        /** @var ExtendOptionsManager $extendOptionsManager */
        $extendOptionsManager = $this->container->get('oro_entity_extend.migration.options_manager');
        $extendOptionsManager->removeColumnOptions('orocrm_sales_lead', 'extend_source');

        $table = $schema->getTable('orocrm_sales_lead');
        if ($table->hasForeignKey('FK_73DB46339C2DD75A')) {
            $table->removeForeignKey('FK_73DB46339C2DD75A');
        }
        if ($table->hasIndex('IDX_73DB46339C2DD75A')) {
            $table->dropIndex('IDX_73DB46339C2DD75A');
        }
        if ($table->hasColumn('extend_source_id')) {
            $table->dropColumn('extend_source_id');
        }
    }
}
