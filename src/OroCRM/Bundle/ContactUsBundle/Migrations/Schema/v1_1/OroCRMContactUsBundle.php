<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMContactUsBundle extends Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addSql(
            $queries->getRenameTableSql('orocrm_contactus_contact_reason', 'orocrm_contactus_contact_rsn')
        );
        $queries->addSql(
            $queries->getRenameTableSql('orocrm_contactus_request_emails', 'orocrm_contactus_req_emails')
        );
    }
}
