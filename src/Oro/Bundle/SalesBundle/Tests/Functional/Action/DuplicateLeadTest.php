<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Action;

use Oro\Bundle\ActionBundle\Model\ActionGroupRegistry;
use Oro\Bundle\ActionBundle\Model\Assembler\ActionGroupAssembler;
use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadLeadWithSourceData;

/**
 * Verifies that the @duplicate action correctly copies extended fields
 *
 * @dbIsolationPerTest
 */
class DuplicateLeadTest extends ActionTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->initClient();
        $this->loadFixtures([LoadLeadWithSourceData::class]);
        $this->registerLeadDuplicateActionGroup();
    }

    public function testDuplicateLeadNullsExtendedSourceField(): void
    {
        /** @var Lead $lead */
        $lead = $this->getReference(LoadLeadWithSourceData::LEAD);

        self::assertNotNull($lead->get('source'), 'Source must be set on the original lead');
        self::assertNotNull($lead->get('status'), 'Status must be set on the original lead');

        $actionData = $this->executeActionGroup('oro_sales_lead_duplicate', [
            'lead' => $lead,
            'flush' => false,
        ]);

        /** @var Lead $copy */
        $copy = $actionData->offsetGet('leadCopy');

        self::assertInstanceOf(Lead::class, $copy);

        // source is explicitly set to null in the duplicate config
        self::assertNull($copy->get('source'));
        // status is NOT nulled — it must be preserved from the original
        self::assertNotNull($copy->get('status'));

        // scalar fields declared as [[keep]] must be preserved
        self::assertSame('Test Lead', $copy->getName());
        self::assertSame('John', $copy->getFirstName());
        self::assertSame('Doe', $copy->getLastName());
        self::assertSame('Test Co', $copy->getCompanyName());
        self::assertSame('Test notes', $copy->getNotes());

        // collections are emptied via [[emptyCollection]]
        self::assertCount(0, $copy->getEmails());

        // nulled by config
        self::assertNull($copy->getId());
        self::assertNull($copy->getOwner());
        self::assertNull($copy->getOrganization());
    }

    private function registerLeadDuplicateActionGroup(): void
    {
        $config = [
            'oro_sales_lead_duplicate' => [
                'parameters' => [
                    'lead' => ['type' => Lead::class],
                    'flush' => ['type' => 'bool', 'default' => true],
                ],
                'actions' => [
                    [
                        '@duplicate' => [
                            'target' => '$.lead',
                            'attribute' => '$.leadCopy',
                            'settings' => [
                                [['setNull'], ['propertyName', ['id']]],
                                [['setNull'], ['propertyName', ['owner']]],
                                [['setNull'], ['propertyName', ['organization']]],
                                [['setNull'], ['propertyName', ['source']]],
                                [['keep'],    ['propertyName', ['name']]],
                                [['keep'],    ['propertyName', ['firstName']]],
                                [['keep'],    ['propertyName', ['lastName']]],
                                [['keep'],    ['propertyName', ['companyName']]],
                                [['keep'],    ['propertyName', ['notes']]],
                                [['emptyCollection'], ['propertyType', ['Doctrine\Common\Collections\Collection']]],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        /** @var ActionGroupAssembler $assembler */
        $assembler = $this->getContainer()->get('oro_action.assembler.action_group');
        $groups = $assembler->assemble($config);

        /** @var ActionGroupRegistry $registry */
        $registry = $this->getContainer()->get('oro_action.action_group_registry');
        // Trigger lazy-loading of existing action groups before injecting ours
        $registry->findByName('__warmup__');
        $prop = new \ReflectionProperty($registry, 'actionGroups');
        $existing = $prop->getValue($registry) ?? [];
        $prop->setValue($registry, array_merge($existing, $groups));
    }
}
