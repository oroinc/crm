<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v2_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\NoteBundle\Migration\UpdateNoteAssociationKindForRenamedEntitiesMigration;

class MigrateNotesRelations extends UpdateNoteAssociationKindForRenamedEntitiesMigration
{
    protected $entitiesNames = [
        'Address',
        'Cart',
        'CartAddress',
        'CartItem',
        'Customer',
        'CustomerGroup',
        'NewsletterSubscriber',
        'Order',
        'OrderAddress',
        'OrderItem',
        'Product',
        'Store',
        'Website',
    ];

    /**
     * {@inheritdoc}
     */
    protected function getRenamedEntitiesNames(Schema $schema)
    {
        $oldNameSpace = 'OroCRM\Bundle\MagentoBundle\Entity';
        $newNameSpace = 'Oro\Bundle\MagentoBundle\Entity';

        $renamedEntityNamesMapping = [];
        foreach ($this->entitiesNames as $entityName) {
            $renamedEntityNamesMapping["$newNameSpace\\$entityName"] = "$oldNameSpace\\$entityName";
        }

        return $renamedEntityNamesMapping;
    }
}
