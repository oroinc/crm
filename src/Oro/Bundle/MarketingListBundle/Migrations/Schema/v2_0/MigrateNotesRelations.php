<?php

namespace Oro\Bundle\MarketingListBundle\Migrations\Schema\v2_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\NoteBundle\Migration\UpdateNoteAssociationKindForRenamedEntitiesMigration;

class MigrateNotesRelations extends UpdateNoteAssociationKindForRenamedEntitiesMigration
{
    /**
     * {@inheritdoc}
     */
    protected function getRenamedEntitiesNames(Schema $schema)
    {
        return [
            'Oro\Bundle\MarketingListBundle\Entity\MarketingList' => 'OroCRM\Bundle\MarketingListBundle' .
                '\Entity\MarketingList'
        ];
    }
}
