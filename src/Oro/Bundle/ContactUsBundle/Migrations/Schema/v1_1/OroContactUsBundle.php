<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroContactUsBundle implements Migration, RenameExtensionAwareInterface
{
    use RenameExtensionAwareTrait;

    #[\Override]
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->renameExtension->renameTable(
            $schema,
            $queries,
            'orocrm_contactus_contact_reason',
            'orocrm_contactus_contact_rsn'
        );
        $this->renameExtension->renameTable(
            $schema,
            $queries,
            'orocrm_contactus_request_emails',
            'orocrm_contactus_req_emails'
        );
    }
}
