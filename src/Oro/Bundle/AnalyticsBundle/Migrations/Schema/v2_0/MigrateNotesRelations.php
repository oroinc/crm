<?php

namespace Oro\Bundle\AnalyticsBundle\Migrations\Schema\v2_0;

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
            'Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory' => 'OroCRM\Bundle\AnalyticsBundle' .
                '\Entity\RFMMetricCategory'
        ];
    }
}
