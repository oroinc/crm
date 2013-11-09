<?php

namespace OroCRM\Bundle\ReportBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use OroCRM\Bundle\ReportBundle\Entity\ReportType;

class LoadReportTypes extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load available report types
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $tableReport = new ReportType('TABLE');
        $tableReport->setLabel('Table');
        $this->addReference('table_report', $tableReport);

        $manager->persist($tableReport);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 40;
    }
}
