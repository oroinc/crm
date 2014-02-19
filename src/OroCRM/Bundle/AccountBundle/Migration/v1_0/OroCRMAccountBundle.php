<?php

namespace OroCRM\Bundle\AccountBundle\Migration\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migrations\Migration;

class OroCRMAccountBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema)
    {
        return [
            "CREATE TABLE orocrm_account (id INT AUTO_INCREMENT NOT NULL, shipping_address_id INT DEFAULT NULL, billing_address_id INT DEFAULT NULL, user_owner_id INT DEFAULT NULL, default_contact_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_7166D3719EB185F9 (user_owner_id), INDEX IDX_7166D3714D4CFF2B (shipping_address_id), INDEX IDX_7166D37179D0C0E4 (billing_address_id), INDEX IDX_7166D371AF827129 (default_contact_id), INDEX account_name_idx (name), PRIMARY KEY(id))",
            "CREATE TABLE orocrm_account_to_contact (account_id INT NOT NULL, contact_id INT NOT NULL, INDEX IDX_65B8FBEC9B6B5FBA (account_id), INDEX IDX_65B8FBECE7A1254A (contact_id), PRIMARY KEY(account_id, contact_id))",

            "ALTER TABLE orocrm_account ADD CONSTRAINT FK_7166D3714D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES oro_address (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_account ADD CONSTRAINT FK_7166D37179D0C0E4 FOREIGN KEY (billing_address_id) REFERENCES oro_address (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_account ADD CONSTRAINT FK_7166D3719EB185F9 FOREIGN KEY (user_owner_id) REFERENCES oro_user (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_account ADD CONSTRAINT FK_7166D371AF827129 FOREIGN KEY (default_contact_id) REFERENCES orocrm_contact (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_account_to_contact ADD CONSTRAINT FK_65B8FBECE7A1254A FOREIGN KEY (contact_id) REFERENCES orocrm_contact (id) ON DELETE CASCADE",
            "ALTER TABLE orocrm_account_to_contact ADD CONSTRAINT FK_65B8FBEC9B6B5FBA FOREIGN KEY (account_id) REFERENCES orocrm_account (id) ON DELETE CASCADE"
        ];
    }
}
