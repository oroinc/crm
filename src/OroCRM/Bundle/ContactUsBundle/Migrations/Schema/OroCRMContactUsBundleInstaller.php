<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use OroCRM\Bundle\ContactUsBundle\Migrations\Schema\v1_0\OroCRMContactUsBundle;

class OroCRMContactUsBundleInstaller implements Installation
{
    /**
     * @inheritdoc
     */
    public function getMigrationVersion()
    {
        return 'v1_1';
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        OroCRMContactUsBundle::orocrmContactusContactReasonTable($schema, 'orocrm_contactus_contact_rsn');
        OroCRMContactUsBundle::orocrmContactusRequestTable($schema);
        OroCRMContactUsBundle::orocrmContactusRequestCallsTable($schema);
        OroCRMContactUsBundle::orocrmContactusRequestEmailsTable($schema, 'orocrm_contactus_req_emails');

        OroCRMContactUsBundle::orocrmContactusRequestForeignKeys($schema, 'orocrm_contactus_contact_rsn');
        OroCRMContactUsBundle::orocrmContactusRequestCallsForeignKeys($schema);
        OroCRMContactUsBundle::orocrmContactusRequestEmailsForeignKeys($schema, 'orocrm_contactus_req_emails');
    }
}
