<?php

namespace Oro\Bundle\CaseBundle\Migrations\Schema\v2_0;

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
            'Oro\Bundle\CaseBundle\Entity\CaseComment' => 'OroCRM\Bundle\CaseBundle\Entity\CaseComment',
            'Oro\Bundle\CaseBundle\Entity\CaseEntity'  => 'OroCRM\Bundle\CaseBundle\Entity\CaseEntity',
        ];
    }
}
