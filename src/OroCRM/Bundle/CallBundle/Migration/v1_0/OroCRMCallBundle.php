<?php

namespace OroCRM\Bundle\CallBundle\Migration\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migrations\Migration;

class OroCRMCallBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema)
    {
        return [
            "CREATE TABLE orocrm_call (id INT AUTO_INCREMENT NOT NULL, call_direction_name VARCHAR(32) DEFAULT NULL, related_account_id INT DEFAULT NULL, related_contact_id INT DEFAULT NULL, call_status_name VARCHAR(32) DEFAULT NULL, owner_id INT DEFAULT NULL, contact_phone_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, phone_number VARCHAR(255) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, call_date_time DATETIME NOT NULL, duration TIME DEFAULT NULL, INDEX IDX_1FBD1A247E3C61F9 (owner_id), INDEX IDX_1FBD1A246D6C2DFA (related_contact_id), INDEX IDX_1FBD1A2411A6570A (related_account_id), INDEX IDX_1FBD1A24A156BF5C (contact_phone_id), INDEX IDX_1FBD1A2476DB3689 (call_status_name), INDEX IDX_1FBD1A249F3E257D (call_direction_name), PRIMARY KEY(id))",
            "CREATE TABLE orocrm_call_direction (name VARCHAR(32) NOT NULL, `label` VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_D0EB34BAEA750E8 (`label`), PRIMARY KEY(name))",
            "CREATE TABLE orocrm_call_status (name VARCHAR(32) NOT NULL, `label` VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_FBA13581EA750E8 (`label`), PRIMARY KEY(name))",

            "ALTER TABLE orocrm_call ADD CONSTRAINT FK_1FBD1A249F3E257D FOREIGN KEY (call_direction_name) REFERENCES orocrm_call_direction (name) ON DELETE SET NULL",
            "ALTER TABLE orocrm_call ADD CONSTRAINT FK_1FBD1A2411A6570A FOREIGN KEY (related_account_id) REFERENCES orocrm_account (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_call ADD CONSTRAINT FK_1FBD1A246D6C2DFA FOREIGN KEY (related_contact_id) REFERENCES orocrm_contact (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_call ADD CONSTRAINT FK_1FBD1A2476DB3689 FOREIGN KEY (call_status_name) REFERENCES orocrm_call_status (name) ON DELETE SET NULL",
            "ALTER TABLE orocrm_call ADD CONSTRAINT FK_1FBD1A247E3C61F9 FOREIGN KEY (owner_id) REFERENCES oro_user (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_call ADD CONSTRAINT FK_1FBD1A24A156BF5C FOREIGN KEY (contact_phone_id) REFERENCES orocrm_contact_phone (id) ON DELETE SET NULL"
        ];
    }
}
