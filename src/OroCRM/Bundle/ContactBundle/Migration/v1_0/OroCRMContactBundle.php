<?php

namespace OroCRM\Bundle\ContactBundle\Migration\v1_0;

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
            "CREATE TABLE orocrm_contact (id INT AUTO_INCREMENT NOT NULL, updated_by_user_id INT DEFAULT NULL, assigned_to_user_id INT DEFAULT NULL, method_name VARCHAR(32) DEFAULT NULL, source_name VARCHAR(32) DEFAULT NULL, created_by_user_id INT DEFAULT NULL, user_owner_id INT DEFAULT NULL, reports_to_contact_id INT DEFAULT NULL, name_prefix VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) NOT NULL, middle_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) NOT NULL, name_suffix VARCHAR(255) DEFAULT NULL, gender VARCHAR(8) DEFAULT NULL, birthday DATETIME DEFAULT NULL, description LONGTEXT DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, fax VARCHAR(255) DEFAULT NULL, skype VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, google_plus VARCHAR(255) DEFAULT NULL, linkedin VARCHAR(255) DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, email VARCHAR(255) DEFAULT NULL, INDEX IDX_403263ED5FA9FB05 (source_name), INDEX IDX_403263ED42F70470 (method_name), INDEX IDX_403263ED9EB185F9 (user_owner_id), INDEX IDX_403263ED11578D11 (assigned_to_user_id), INDEX IDX_403263EDF27EBC1E (reports_to_contact_id), INDEX IDX_403263ED7D182D95 (created_by_user_id), INDEX IDX_403263ED2793CC5E (updated_by_user_id), PRIMARY KEY(id))",
            "CREATE TABLE orocrm_contact_address (id INT AUTO_INCREMENT NOT NULL, region_code VARCHAR(16) DEFAULT NULL, owner_id INT DEFAULT NULL, country_code VARCHAR(2) DEFAULT NULL, is_primary TINYINT(1) DEFAULT NULL, `label` VARCHAR(255) DEFAULT NULL, street VARCHAR(500) NOT NULL, street2 VARCHAR(500) DEFAULT NULL, city VARCHAR(255) NOT NULL, postal_code VARCHAR(20) NOT NULL, organization VARCHAR(255) DEFAULT NULL, region_text VARCHAR(255) DEFAULT NULL, name_prefix VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, middle_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, name_suffix VARCHAR(255) DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_CACC16DB7E3C61F9 (owner_id), INDEX IDX_CACC16DBF026BB7C (country_code), INDEX IDX_CACC16DBAEB327AF (region_code), PRIMARY KEY(id))",
            "CREATE TABLE orocrm_contact_address_to_address_type (contact_address_id INT NOT NULL, type_name VARCHAR(16) NOT NULL, INDEX IDX_3FBCDDC6320EF6E2 (contact_address_id), INDEX IDX_3FBCDDC6892CBB0E (type_name), PRIMARY KEY(contact_address_id, type_name))",
            "CREATE TABLE orocrm_contact_email (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, is_primary TINYINT(1) DEFAULT NULL, INDEX IDX_335A28C37E3C61F9 (owner_id), INDEX primary_email_idx (email, is_primary), PRIMARY KEY(id))",
            "CREATE TABLE orocrm_contact_group (id SMALLINT AUTO_INCREMENT NOT NULL, user_owner_id INT DEFAULT NULL, `label` VARCHAR(30) NOT NULL, UNIQUE INDEX UNIQ_B9081072EA750E8 (`label`), INDEX IDX_B90810729EB185F9 (user_owner_id), PRIMARY KEY(id))",
            "CREATE TABLE orocrm_contact_method (name VARCHAR(32) NOT NULL, `label` VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B88D41BEA750E8 (`label`), PRIMARY KEY(name))",
            "CREATE TABLE orocrm_contact_phone (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, phone VARCHAR(255) NOT NULL, is_primary TINYINT(1) DEFAULT NULL, INDEX IDX_9087C36A7E3C61F9 (owner_id), INDEX primary_phone_idx (phone, is_primary), PRIMARY KEY(id))",
            "CREATE TABLE orocrm_contact_source (name VARCHAR(32) NOT NULL, `label` VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_A5B9108EA750E8 (`label`), PRIMARY KEY(name))",
            "CREATE TABLE orocrm_contact_to_contact_group (contact_id INT NOT NULL, contact_group_id SMALLINT NOT NULL, INDEX IDX_885CCB12E7A1254A (contact_id), INDEX IDX_885CCB12647145D0 (contact_group_id), PRIMARY KEY(contact_id, contact_group_id))",

            "ALTER TABLE orocrm_contact ADD CONSTRAINT FK_403263ED2793CC5E FOREIGN KEY (updated_by_user_id) REFERENCES oro_user (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_contact ADD CONSTRAINT FK_403263ED11578D11 FOREIGN KEY (assigned_to_user_id) REFERENCES oro_user (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_contact ADD CONSTRAINT FK_403263ED42F70470 FOREIGN KEY (method_name) REFERENCES orocrm_contact_method (name)",
            "ALTER TABLE orocrm_contact ADD CONSTRAINT FK_403263ED5FA9FB05 FOREIGN KEY (source_name) REFERENCES orocrm_contact_source (name)",
            "ALTER TABLE orocrm_contact ADD CONSTRAINT FK_403263ED7D182D95 FOREIGN KEY (created_by_user_id) REFERENCES oro_user (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_contact ADD CONSTRAINT FK_403263ED9EB185F9 FOREIGN KEY (user_owner_id) REFERENCES oro_user (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_contact ADD CONSTRAINT FK_403263EDF27EBC1E FOREIGN KEY (reports_to_contact_id) REFERENCES orocrm_contact (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_contact_address ADD CONSTRAINT FK_CACC16DBAEB327AF FOREIGN KEY (region_code) REFERENCES oro_dictionary_region (combined_code)",
            "ALTER TABLE orocrm_contact_address ADD CONSTRAINT FK_CACC16DB7E3C61F9 FOREIGN KEY (owner_id) REFERENCES orocrm_contact (id)",
            "ALTER TABLE orocrm_contact_address ADD CONSTRAINT FK_CACC16DBF026BB7C FOREIGN KEY (country_code) REFERENCES oro_dictionary_country (iso2_code)",
            "ALTER TABLE orocrm_contact_address_to_address_type ADD CONSTRAINT FK_3FBCDDC6892CBB0E FOREIGN KEY (type_name) REFERENCES oro_address_type (name)",
            "ALTER TABLE orocrm_contact_address_to_address_type ADD CONSTRAINT FK_3FBCDDC6320EF6E2 FOREIGN KEY (contact_address_id) REFERENCES orocrm_contact_address (id)",
            "ALTER TABLE orocrm_contact_email ADD CONSTRAINT FK_335A28C37E3C61F9 FOREIGN KEY (owner_id) REFERENCES orocrm_contact (id)",
            "ALTER TABLE orocrm_contact_group ADD CONSTRAINT FK_B90810729EB185F9 FOREIGN KEY (user_owner_id) REFERENCES oro_user (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_contact_phone ADD CONSTRAINT FK_9087C36A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES orocrm_contact (id)",
            "ALTER TABLE orocrm_contact_to_contact_group ADD CONSTRAINT FK_885CCB12647145D0 FOREIGN KEY (contact_group_id) REFERENCES orocrm_contact_group (id) ON DELETE CASCADE",
            "ALTER TABLE orocrm_contact_to_contact_group ADD CONSTRAINT FK_885CCB12E7A1254A FOREIGN KEY (contact_id) REFERENCES orocrm_contact (id) ON DELETE CASCADE",

            // Add contact as owner to oro_email_address table
            "ALTER TABLE oro_email_address ADD COLUMN owner_contact_id INT NULL AFTER id, ADD INDEX IDX_FC9DBBC5B5CBBC0F (owner_contact_id);",
            "ALTER TABLE oro_email_address ADD CONSTRAINT FK_FC9DBBC5B5CBBC0F FOREIGN KEY (owner_contact_id) REFERENCES orocrm_contact (id)",
        ];
    }
}
