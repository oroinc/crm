<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Functional\Command;

use Doctrine\ORM\EntityManager;
use Monolog\Registry;
use Oro\Bundle\ActivityContactBundle\Command\ActivityContactRecalculateCommand;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\DotmailerBundle\Entity\Contact;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ActivityContactRecalculateCommandTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([
            'Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData',
        ]);
    }

    public function testRecalculationOfContactedContactHavingNoActivities()
    {
        $firstContact = $this->findContact(LoadContactEntitiesData::FIRST_ENTITY_NAME);

        $firstContact->setAcContactCount(1);
        $this->getEntityManager()->flush();
        $this->runActivityContactRecalculateCommand();
        $firstContact = $this->findContact(LoadContactEntitiesData::FIRST_ENTITY_NAME);

        $this->assertEquals(0, $firstContact->getAcContactCount());
    }

    protected function runActivityContactRecalculateCommand()
    {
        $this->runCommand(ActivityContactRecalculateCommand::getDefaultName());
    }

    /**
     * @param string $firstName
     *
     * @return Contact
     */
    protected function findContact($firstName)
    {
        return $this->getRegistry()->getRepository('OroContactBundle:Contact')->findOneByFirstName($firstName);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getRegistry()->getManager();
    }

    /**
     * @return Registry
     */
    protected function getRegistry()
    {
        return $this->getContainer()->get('doctrine');
    }
}
