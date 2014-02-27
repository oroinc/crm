<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use OroCRM\Bundle\ContactBundle\Migrations\Schema\v1_0\OroCRMContactBundle;

class OroCRMContactBundleInstaller implements Installation
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
        OroCRMContactBundle::orocrmContactTable($schema);
        OroCRMContactBundle::orocrmContactAddressTable($schema);
        OroCRMContactBundle::orocrmContactAddressToAddressTypeTable($schema, 'orocrm_contact_adr_to_adr_type');
        OroCRMContactBundle::orocrmContactEmailTable($schema);
        OroCRMContactBundle::orocrmContactGroupTable($schema);
        OroCRMContactBundle::orocrmContactMethodTable($schema);
        OroCRMContactBundle::orocrmContactPhoneTable($schema);
        OroCRMContactBundle::orocrmContactSourceTable($schema);
        OroCRMContactBundle::orocrmContactToContactGroupTable($schema, 'orocrm_contact_to_contact_grp');

        OroCRMContactBundle::orocrmContactForeignKeys($schema);
        OroCRMContactBundle::orocrmContactAddressForeignKeys($schema);
        OroCRMContactBundle::orocrmContactAddressToAddressTypeForeignKeys($schema, 'orocrm_contact_adr_to_adr_type');
        OroCRMContactBundle::orocrmContactEmailForeignKeys($schema);
        OroCRMContactBundle::orocrmContactGroupForeignKeys($schema);
        OroCRMContactBundle::orocrmContactPhoneForeignKeys($schema);
        OroCRMContactBundle::orocrmContactToContactGroupForeignKeys($schema, 'orocrm_contact_to_contact_grp');
        OroCRMContactBundle::oroEmailAddressForeignKeys($schema, 'orocrm_contact_to_contact_grp');

        return [];
    }
}
