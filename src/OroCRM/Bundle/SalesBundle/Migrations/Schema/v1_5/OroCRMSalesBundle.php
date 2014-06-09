<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;

class OroCRMSalesBundle implements Migration, NoteExtensionAwareInterface
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
     * Enable notes for Lead and Opportunity entities
     *
     * @param Schema        $schema
     * @param NoteExtension $noteExtension
     */
    public static function addNoteAssociations(Schema $schema, NoteExtension $noteExtension)
    {
        $noteExtension->addNoteAssociation($schema, 'orocrm_sales_lead');
        $noteExtension->addNoteAssociation($schema, 'orocrm_sales_opportunity');
    }
}
