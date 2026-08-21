<?php

namespace Oro\Bundle\ContactBundle\Migrations\Schema\v1_21;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Enables webhook access for the Contact entity on existing installations.
 *
 * The value is updated only where webhooks are currently disabled, so an entity an administrator
 * has already enabled is left untouched.
 */
class EnableWebhookAccess implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $queries->addPostQuery(new UpdateEntityConfigEntityValueQuery(
            Contact::class,
            'integration',
            'webhook_accessible',
            true,
            false
        ));
    }
}
