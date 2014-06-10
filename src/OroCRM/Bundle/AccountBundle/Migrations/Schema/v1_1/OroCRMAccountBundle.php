<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;

class OroCRMAccountBundle implements Migration, NoteExtensionAwareInterface
{
    /** @var NoteExtension */
    protected $noteExtension;

    /**
     * {@inheritdoc}
     */
    public function setNoteExtension(NoteExtension $noteExtension)
    {
        $this->noteExtension = $noteExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addNoteAssociations($schema, $this->noteExtension);
    }

    /**
     * Enable notes for Account entity
     *
     * @param Schema        $schema
     * @param NoteExtension $noteExtension
     */
    public static function addNoteAssociations(Schema $schema, NoteExtension $noteExtension)
    {
        $noteExtension->addNoteAssociation($schema, 'orocrm_account');
    }
}
