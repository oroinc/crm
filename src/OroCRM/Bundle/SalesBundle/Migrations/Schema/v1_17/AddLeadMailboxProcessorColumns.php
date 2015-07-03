<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_17;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddLeadMailboxProcessorColumns implements Migration, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_email_mailbox_processor');
        $table->addColumn('lead_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('lead_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('lead_source_id', 'integer', ['notnull' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
