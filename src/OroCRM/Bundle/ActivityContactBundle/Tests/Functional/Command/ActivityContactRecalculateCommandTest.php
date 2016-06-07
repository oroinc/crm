<?php

namespace OroCRM\Bundle\ActivityContactBundle\Tests\Functional\Command;

use Doctrine\ORM\EntityManager;

use Monolog\Registry;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\ActivityContactBundle\Command\ActivityContactRecalculateCommand;
use OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use OroCRM\Bundle\DotmailerBundle\Entity\Contact;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ActivityContactRecalculateCommandTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient();
        $this->loadFixtures([
            'OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData',
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
        $app = new Application($this->getContainer()->get('kernel'));
        $app->setAutoExit(false);
        $app->run(new ArrayInput([
            'command' => ActivityContactRecalculateCommand::COMMAND_NAME,
        ]));
    }

    /**
     * @param string $firstName
     *
     * @return Contact
     */
    protected function findContact($firstName)
    {
        return $this->getRegistry()->getRepository('OroCRMContactBundle:Contact')->findOneByFirstName($firstName);
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
