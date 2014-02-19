<?php

namespace OroCRM\Bundle\SalesBundle\Migration\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migrations\Migration;

class OroCRMSalesBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema)
    {
        return [
            "CREATE TABLE orocrm_sales_lead (id INT AUTO_INCREMENT NOT NULL, workflow_step_id INT DEFAULT NULL, workflow_item_id INT DEFAULT NULL, status_name VARCHAR(32) DEFAULT NULL, account_id INT DEFAULT NULL, user_owner_id INT DEFAULT NULL, contact_id INT DEFAULT NULL, address_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, name_prefix VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) NOT NULL, middle_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) NOT NULL, name_suffix VARCHAR(255) DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, company_name VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, number_of_employees INT DEFAULT NULL, industry VARCHAR(255) DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, notes LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_73DB46331023C4EE (workflow_item_id), INDEX IDX_73DB46336625D392 (status_name), INDEX IDX_73DB4633E7A1254A (contact_id), INDEX IDX_73DB46339B6B5FBA (account_id), INDEX IDX_73DB4633F5B7AF75 (address_id), INDEX IDX_73DB46339EB185F9 (user_owner_id), INDEX IDX_73DB463371FE882C (workflow_step_id), PRIMARY KEY(id))",
            "CREATE TABLE orocrm_sales_lead_status (name VARCHAR(32) NOT NULL, `label` VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_4516951BEA750E8 (`label`), PRIMARY KEY(name))",
            "CREATE TABLE orocrm_sales_opportunity (id INT AUTO_INCREMENT NOT NULL, workflow_step_id INT DEFAULT NULL, workflow_item_id INT DEFAULT NULL, lead_id INT DEFAULT NULL, status_name VARCHAR(32) DEFAULT NULL, account_id INT DEFAULT NULL, user_owner_id INT DEFAULT NULL, close_reason_name VARCHAR(32) DEFAULT NULL, contact_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, close_date DATE DEFAULT NULL, probability DOUBLE PRECISION DEFAULT NULL, budget_amount DOUBLE PRECISION DEFAULT NULL, close_revenue DOUBLE PRECISION DEFAULT NULL, customer_need VARCHAR(255) DEFAULT NULL, proposed_solution VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, notes LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_C0FE4AAC1023C4EE (workflow_item_id), INDEX IDX_C0FE4AAC6625D392 (status_name), INDEX IDX_C0FE4AACD81B931C (close_reason_name), INDEX IDX_C0FE4AACE7A1254A (contact_id), INDEX IDX_C0FE4AAC9B6B5FBA (account_id), INDEX IDX_C0FE4AAC55458D (lead_id), INDEX IDX_C0FE4AAC9EB185F9 (user_owner_id), INDEX IDX_C0FE4AAC71FE882C (workflow_step_id), PRIMARY KEY(id))",
            "CREATE TABLE orocrm_sales_opportunity_close_reason (name VARCHAR(32) NOT NULL, `label` VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_FA526A41EA750E8 (`label`), PRIMARY KEY(name))",
            "CREATE TABLE orocrm_sales_opportunity_status (name VARCHAR(32) NOT NULL, `label` VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2DB212B5EA750E8 (`label`), PRIMARY KEY(name))",

            "ALTER TABLE orocrm_sales_lead ADD CONSTRAINT FK_73DB463371FE882C FOREIGN KEY (workflow_step_id) REFERENCES oro_workflow_step (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_sales_lead ADD CONSTRAINT FK_73DB46331023C4EE FOREIGN KEY (workflow_item_id) REFERENCES oro_workflow_item (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_sales_lead ADD CONSTRAINT FK_73DB46336625D392 FOREIGN KEY (status_name) REFERENCES orocrm_sales_lead_status (name)",
            "ALTER TABLE orocrm_sales_lead ADD CONSTRAINT FK_73DB46339B6B5FBA FOREIGN KEY (account_id) REFERENCES orocrm_account (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_sales_lead ADD CONSTRAINT FK_73DB46339EB185F9 FOREIGN KEY (user_owner_id) REFERENCES oro_user (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_sales_lead ADD CONSTRAINT FK_73DB4633E7A1254A FOREIGN KEY (contact_id) REFERENCES orocrm_contact (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_sales_lead ADD CONSTRAINT FK_73DB4633F5B7AF75 FOREIGN KEY (address_id) REFERENCES oro_address (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_sales_opportunity ADD CONSTRAINT FK_C0FE4AAC71FE882C FOREIGN KEY (workflow_step_id) REFERENCES oro_workflow_step (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_sales_opportunity ADD CONSTRAINT FK_C0FE4AAC1023C4EE FOREIGN KEY (workflow_item_id) REFERENCES oro_workflow_item (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_sales_opportunity ADD CONSTRAINT FK_C0FE4AAC55458D FOREIGN KEY (lead_id) REFERENCES orocrm_sales_lead (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_sales_opportunity ADD CONSTRAINT FK_C0FE4AAC6625D392 FOREIGN KEY (status_name) REFERENCES orocrm_sales_opportunity_status (name)",
            "ALTER TABLE orocrm_sales_opportunity ADD CONSTRAINT FK_C0FE4AAC9B6B5FBA FOREIGN KEY (account_id) REFERENCES orocrm_account (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_sales_opportunity ADD CONSTRAINT FK_C0FE4AAC9EB185F9 FOREIGN KEY (user_owner_id) REFERENCES oro_user (id) ON DELETE SET NULL",
            "ALTER TABLE orocrm_sales_opportunity ADD CONSTRAINT FK_C0FE4AACD81B931C FOREIGN KEY (close_reason_name) REFERENCES orocrm_sales_opportunity_close_reason (name)",
            "ALTER TABLE orocrm_sales_opportunity ADD CONSTRAINT FK_C0FE4AACE7A1254A FOREIGN KEY (contact_id) REFERENCES orocrm_contact (id) ON DELETE SET NULL"
        ];
    }
}
