<?php

namespace Oro\Bundle\ContactBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroContactBundle implements Migration, RenameExtensionAwareInterface
{
    use RenameExtensionAwareTrait;

    #[\Override]
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->renameExtension->renameTable(
            $schema,
            $queries,
            'orocrm_contact_to_contact_group',
            'orocrm_contact_to_contact_grp'
        );
        $this->renameExtension->renameTable(
            $schema,
            $queries,
            'orocrm_contact_address_to_address_type',
            'orocrm_contact_adr_to_adr_type'
        );
    }
}
