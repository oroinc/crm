<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_20;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManagerAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManagerAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveExtendSourceField implements Migration, ExtendOptionsManagerAwareInterface
{
    use ExtendOptionsManagerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $queries->addQuery(new RemoveExtendSourceFieldQuery());

        $this->extendOptionsManager->removeColumnOptions('orocrm_sales_lead', 'extend_source');

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
