<?php

namespace OroCRM\Bundle\ContactUsBundle\Migration\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migrations\Migration;

class OroCRMContactUsBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema)
    {
        return [
            "CREATE TABLE oro_contact_request (id SMALLINT AUTO_INCREMENT NOT NULL, channel_id SMALLINT DEFAULT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, comment LONGTEXT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_BB185C2A72F5A1AA (channel_id), PRIMARY KEY(id))",

            "ALTER TABLE oro_contact_request ADD CONSTRAINT FK_BB185C2A72F5A1AA FOREIGN KEY (channel_id) REFERENCES oro_integration_channel (id)"
        ];
    }
}
