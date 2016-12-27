<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v2_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\NoteBundle\Migration\UpdateNoteAssociationKindForRenamedEntitiesMigration;

class MigrateNotesRelations extends UpdateNoteAssociationKindForRenamedEntitiesMigration
{
    protected $entitiesNames = [
        'ContactReason',
        'ContactRequest',
    ];

    /**
     * {@inheritdoc}
     */
    protected function getRenamedEntitiesNames(Schema $schema)
    {
        $oldNameSpace = 'OroCRM\Bundle\ContactUsBundle\Entity';
        $newNameSpace = 'Oro\Bundle\ContactUsBundle\Entity';

        $renamedEntityNamesMapping = [];
        foreach ($this->entitiesNames as $entityName) {
            $renamedEntityNamesMapping["$newNameSpace\\$entityName"] = "$oldNameSpace\\$entityName";
        }

        return $renamedEntityNamesMapping;
    }
}
