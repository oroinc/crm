<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Schemas\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migrations\Migration;

class OroCRMContactBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema)
    {
        return [
            "ALTER TABLE orocrm_contact_to_contact_group RENAME TO orocrm_contact_to_contact_grp;",
            "ALTER TABLE orocrm_contact_address_to_address_type RENAME TO orocrm_contact_adr_to_adr_type;",
        ];
    }
}
