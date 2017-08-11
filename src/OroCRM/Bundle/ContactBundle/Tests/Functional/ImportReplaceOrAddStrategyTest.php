<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional;

use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository as BatchJobRepository;

use Doctrine\ORM\EntityManager;

use Symfony\Component\DomCrawler\Form;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

/**
 * @outputBuffering enabled
 * @dbIsolationPerTest
 * @dbReindex
 */
class ImportReplaceOrAddStrategyTest extends WebTestCase
{
    const ADD_STRATEGY              = 'orocrm_contact.add';
    const ADD_OR_REPLACE_STRATEGY   = 'orocrm_contact.add_or_replace';

    /** @var string */
    protected $file;

    /** {@inheritdoc} */
    protected function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader());
    }

    /**
     * Delete data required because there is commit to job repository in import/export controller action
     * Please use
     *   $this->getContainer()->get('akeneo_batch.job_repository')->getJobManager()->beginTransaction();
     *   $this->getContainer()->get('akeneo_batch.job_repository')->getJobManager()->rollback();
     *   $this->getContainer()->get('akeneo_batch.job_repository')->getJobManager()->getConnection()->clear();
     * if you don't use controller
     */
    protected function tearDown()
    {
        // clear DB from separate connection, close to avoid connection limit and memory leak
        $batchJobManager = $this->getBatchJobManager();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:JobInstance')->execute();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:JobExecution')->execute();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:StepExecution')->execute();

        unset($this->file);

        parent::tearDown();
    }

    /**
     * Test how import contact replace strategy. More see CRM-8363
     */
    public function testAddOrReplaceStrategyImport()
    {
        $this->initDataFixtureFile('contacts.csv');

        $this->validateImportFile(self::ADD_OR_REPLACE_STRATEGY);
        $this->doImport(self::ADD_OR_REPLACE_STRATEGY, 10, 0);

        $this->validateImportFile(self::ADD_OR_REPLACE_STRATEGY);
        $this->doImport(self::ADD_OR_REPLACE_STRATEGY, 0, 10);
    }

    /**
     * Test how import data twice after delete. More see CRM-8364
     */
    public function testAddOrStrategyImport()
    {
        $this->initDataFixtureFile('contacts.csv');

        $this->validateImportFile(self::ADD_OR_REPLACE_STRATEGY);
        $this->doImport(self::ADD_OR_REPLACE_STRATEGY, 10, 0);

        $this->deleteTableRecords();

        $this->validateImportFile(self::ADD_OR_REPLACE_STRATEGY);
        $this->doImport(self::ADD_OR_REPLACE_STRATEGY, 10, 0);
    }

    public function testAddOrReplaceStrategyWithDuplicateRecordsImport()
    {
        $this->initDataFixtureFile('contact_with_duplicate_records.csv');

        $this->validateImportFile(self::ADD_OR_REPLACE_STRATEGY);
        $this->doImport(self::ADD_OR_REPLACE_STRATEGY, 1, 1);
    }

    protected function deleteTableRecords()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $collection = $em->getRepository('OroCRMContactBundle:Contact')->findAll();

        foreach ($collection as $item) {
            $em->remove($item);
        }

        $em->flush();
    }

    /**
     * @param string $strategy
     */
    protected function validateImportFile($strategy)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_importexport_import_form',
                array(
                    'entity'           => Contact::class,
                    '_widgetContainer' => 'dialog'
                )
            )
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains($strategy, $result->getContent());

        $this->assertTrue(file_exists($this->file));

        /** @var Form $form */
        $form = $crawler->selectButton('Submit')->form();

        /** TODO Change after BAP-1813 */
        $form->getFormNode()->setAttribute(
            'action',
            $form->getFormNode()->getAttribute('action') . '&_widgetContainer=dialog'
        );

        $form['oro_importexport_import[file]']->upload($this->file);
        $form['oro_importexport_import[processorAlias]'] = $strategy;

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $crawler = $this->client->getCrawler();
        $this->assertEquals(0, $crawler->filter('.import-errors')->count());
    }

    /**
     * @param string $strategy
     * @param int $added
     * @param int $replaced
     */
    protected function doImport($strategy, $added, $replaced)
    {
        // test import
        $this->client->followRedirects(false);
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_importexport_import_process',
                array(
                    'processorAlias' => $strategy,
                    '_format'        => 'json'
                )
            )
        );

        $data = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals(
            [
                'success'    => true,
                'message'    => 'File was successfully imported.',
                'errorsUrl'  => null,
                'importInfo' => sprintf('%s entities were added, %s entities were updated', $added, $replaced)
            ],
            $data
        );
    }

    /**
     * @return EntityManager
     */
    protected function getBatchJobManager()
    {
        /** @var BatchJobRepository $batchJobRepository */
        $batchJobRepository = $this->getContainer()->get('akeneo_batch.job_repository');

        return $batchJobRepository->getJobManager();
    }

    /**
     * @param $fileName
     */
    protected function initDataFixtureFile($fileName)
    {
        $dataDir = $this->getContainer()
            ->get('kernel')
            ->locateResource('@OroCRMContactBundle/Tests/Functional/DataFixtures/Data');

        $this->file = $dataDir . DIRECTORY_SEPARATOR. $fileName;
    }
}
