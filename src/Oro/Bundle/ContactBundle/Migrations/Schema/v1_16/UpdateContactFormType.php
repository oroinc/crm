<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Schema\v3_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateContactFormType implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                Contact::class,
                'form',
                'form_type',
                ContactSelectType::class
            )
        );
    }
}
