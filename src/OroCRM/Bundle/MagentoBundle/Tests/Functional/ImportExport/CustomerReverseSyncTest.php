<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\ImportExport\CustomerReverseSync;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class CustomerReverseSyncTest extends WebTestCase
{
    const FIXTURE_NS = 'OroCRM\\Bundle\\MagentoBundle\\Tests\\Functional\\Fixture\\';

    /** @var int */
    protected static $integrationId;

    /** @var int */
    protected static $contactId;

    public function setUp()
    {
        $this->initClient([], array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1]));
        $fixtures = [
            self::FIXTURE_NS . 'LoadMagentoChannel',
            self::FIXTURE_NS . 'LoadCustomerContact'
        ];
        $this->loadFixtures($fixtures);
    }

    protected function postFixtureLoad()
    {
        $integration = $this->getReference('integration');
        $contact     = $this->getReference('contact');

        if (!($integration && $contact)) {
            $this->markTestIncomplete('Invalid fixtures, unable to perform test case');
        }

        self::$contactId     = $contact->getId();
        self::$integrationId = $integration->getId();
    }

    /**
     * @dataProvider jobScheduleProvider
     *
     * @param bool $twoWaySyncEnabled
     * @param int  $expectedJobsCount
     */
    public function testJobScheduling($twoWaySyncEnabled, $expectedJobsCount)
    {
        $em      = $this->getEM();
        $channel = $em->find('OroIntegrationBundle:Channel', self::$integrationId);

        $settings = $channel->getSynchronizationSettings();
        $settings->offsetSet('isTwoWaySyncEnabled', $twoWaySyncEnabled);
        $channel->setSynchronizationSettings($settings);
        $em->flush();

        $this->assertEmpty($this->getSyncJobsCount(), 'Sync job exists on start of the test');

        // update contact via UI needed by requirements
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orocrm_contact_update', ['id' => self::$contactId])
        );

        $form                                   = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_contact_form[firstName]'] = uniqid('Contact_fname_updated');
        $form['orocrm_contact_form[lastName]']  = uniqid('Contact_lname_updated');

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertEquals(
            $expectedJobsCount,
            $this->getSyncJobsCount('JMSJobQueueBundle:Job'),
            'Only one sync job must be created depends on channel setting'
        );
    }

    /**
     * @return array
     */
    public function jobScheduleProvider()
    {
        return [
            'should not create job, sync disabled' => [false, 0],
            'should create job, sync enabled'      => [true, 1],
        ];
    }

    /**
     * @return \Oro\Bundle\EntityBundle\ORM\OroEntityManager
     */
    protected function getEM()
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @return integer
     */
    protected function getSyncJobsCount()
    {
        return (int)$this->getEM()->createQueryBuilder()
            ->select('count(j)')
            ->from('JMSJobQueueBundle:Job', 'j')
            ->where('j.command = :command')->setParameter('command', 'oro:integration:reverse:sync')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
