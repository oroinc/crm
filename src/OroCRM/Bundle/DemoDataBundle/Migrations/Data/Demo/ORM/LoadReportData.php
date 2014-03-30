<?php
namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\ReportBundle\Entity\Report;
use Oro\Bundle\ReportBundle\Entity\ReportType;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Doctrine\ORM\EntityManager;

class LoadReportData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var  EntityManager */
    protected $em;

    // @codingStandardsIgnoreStart
    protected $reports = array(
        array(
            'name' => 'Total Forecast',
            'description' => 'This report forecasts maximum projected income from all current opportunities.',
            'type' => 'table',
            'owner' => 'Acme, General',
            'entity' => 'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
            'definition' => '{"filters":[{"columnName":"probability","criterion":{"filter":"number","data":{"value":0,"type":"4"}}},"AND",{"columnName":"probability","criterion":{"filter":"number","data":{"value":1,"type":"4"}}}],"grouping_columns":[{"name":"probability","oro_report_form[grouping][columnNames]":"probability"}],"columns":[{"name":"budgetAmount","label":"Budget amount","func":{"name":"Sum","group_type":"aggregates","group_name":"number"},"sorting":""},{"name":"probability","label":"Probability","func":"","sorting":"DESC"}]}'
        ),
        array(
            'name' => 'Leads by Geography',
            'description' => 'Geographical distribution of Leads',
            'type' => 'table',
            'owner' => 'Acme, General',
            'entity' => 'OroCRM\Bundle\SalesBundle\Entity\Lead',
            'definition' => '	{"filters":[],"grouping_columns":[{"name":"address+Oro\\\\Bundle\\\\AddressBundle\\\\Entity\\\\Address::region+Oro\\\\Bundle\\\\AddressBundle\\\\Entity\\\\Region::name","oro_report_form[grouping][columnNames]":"address+Oro\\\\Bundle\\\\AddressBundle\\\\Entity\\\\Address::region+Oro\\\\Bundle\\\\AddressBundle\\\\Entity\\\\Region::name"}],"columns":[{"name":"address+Oro\\\\Bundle\\\\AddressBundle\\\\Entity\\\\Address::region+Oro\\\\Bundle\\\\AddressBundle\\\\Entity\\\\Region::name","label":"State","func":"","sorting":"ASC"},{"name":"id","label":"NUMBER OF LEADS","func":{"name":"Count","group_type":"aggregates","group_name":"number"},"sorting":""}]}'
        ),
    );
    // @codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadBusinessUnitData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities($manager);
        $this->loadReports();
    }

    protected function initSupportingEntities(ObjectManager $manager = null)
    {
        if ($manager) {
            $this->em = $manager;
        }
    }

    public function loadReports()
    {
        foreach ($this->reports as $values) {
            $report = new Report();
            $report->setName($values['name']);
            $report->setDescription($values['description']);
            $report->setEntity($values['entity']);
            /** @var ReportType $type */
            $type = $this->em
                ->getRepository('OroReportBundle:ReportType')
                ->findOneBy(array('name' => $values['type']));
            $report->setType($type);
            /** @var BusinessUnit $owner */
            $owner = $this->em
                ->getRepository('OroOrganizationBundle:BusinessUnit')
                ->findOneBy(array('name' => $values['owner']));
            $report->setOwner($owner);
            $report->setDefinition($values['definition']);
            $this->persist($this->em, $report);
        }

        $this->flush($this->em);

    }

    /**
     * Persist object
     *
     * @param mixed $manager
     * @param mixed $object
     */
    private function persist($manager, $object)
    {
        $manager->persist($object);
    }

    /**
     * Flush objects
     *
     * @param mixed $manager
     */
    private function flush($manager)
    {
        $manager->flush();
    }
}
