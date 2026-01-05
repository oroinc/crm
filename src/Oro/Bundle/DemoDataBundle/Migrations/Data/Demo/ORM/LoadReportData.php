<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\ReportBundle\Entity\Report;
use Oro\Bundle\ReportBundle\Entity\ReportType;
use Oro\Bundle\SalesBundle\Entity\Lead;

/**
 * Loads "Leads by Geography" report.
 */
class LoadReportData extends AbstractFixture implements DependentFixtureInterface
{
    // phpcs:disable
    private array $reports = [
        [
            'name' => 'Leads by Geography',
            'description' => 'Geographical distribution of Leads',
            'type' => ReportType::TYPE_TABLE,
            'owner' => 'Acme, General',
            'entity' => Lead::class,
            'definition' => '{"filters":[],"grouping_columns":[{"name":"addresses+Oro\\\\Bundle\\\\SalesBundle\\\\Entity\\\\LeadAddress::region_name"}],"columns":[{"name":"addresses+Oro\\\\Bundle\\\\SalesBundle\\\\Entity\\\\LeadAddress::region_name","label":"State","func":"","sorting":"ASC"},{"name":"id","label":"Number of Leads","func":{"name":"Count","group_type":"aggregates","group_name":"number"},"sorting":""}]}'
        ],
    ];
    // phpcs:enable

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadBusinessUnitData::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $reportTypeRepository = $manager->getRepository(ReportType::class);
        $businessUnitRepository = $manager->getRepository(BusinessUnit::class);
        $organization = $this->getReference('default_organization');
        foreach ($this->reports as $values) {
            $report = new Report();
            $report->setName($values['name']);
            $report->setDescription($values['description']);
            $report->setEntity($values['entity']);
            $report->setType($reportTypeRepository->findOneBy(['name' => $values['type']]));
            $report->setOwner($businessUnitRepository->findOneBy(['name' => $values['owner']]));
            $report->setDefinition($values['definition']);
            $report->setOrganization($organization);
            $manager->persist($report);
        }
        $manager->flush();
    }
}
