<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migrations\Installation;
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function up(Schema $schema)
    {
        OroCRMContactUsBundle::orocrmContactusContactReasonTable($schema, 'orocrm_contactus_contact_reas');
        OroCRMContactUsBundle::orocrmContactusRequestTable($schema);
        OroCRMContactUsBundle::orocrmContactusRequestCallsTable($schema);
        OroCRMContactUsBundle::orocrmContactusRequestEmailsTable($schema, 'orocrm_contactus_req_emails');

        OroCRMContactUsBundle::orocrmContactusRequestForeignKeys($schema, 'orocrm_contactus_contact_reas');
        OroCRMContactUsBundle::orocrmContactusRequestCallsForeignKeys($schema);
        OroCRMContactUsBundle::orocrmContactusRequestEmailsForeignKeys($schema, 'orocrm_contactus_req_emails');

        return [];
    }
}
