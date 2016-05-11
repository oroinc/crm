<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional;

use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository as BatchJobRepository;

use Symfony\Component\DomCrawler\Form;

use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @dbReindex
 */
class ImportExportTest extends WebTestCase
{
    /**
     * @var string
     */
    protected $file;

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

        parent::tearDown();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getBatchJobManager()
    {
        /** @var BatchJobRepository $batchJobRepository */
        $batchJobRepository = $this->getContainer()->get('akeneo_batch.job_repository');
        return $batchJobRepository->getJobManager();
    }

    public function strategyDataProvider()
    {
        return [
            'add'            => ['orocrm_contact.add', 1, 0],
            'add or replace' => ['orocrm_contact.add_or_replace', 0, 1],
        ];
    }

    /**
     * @param string $strategy
     * @param int $added
     * @param int $replaced
     * @dataProvider strategyDataProvider
     */
    public function testImportExport($strategy, $added, $replaced)
    {
        $this->validateImportFile($strategy);
        $this->doImport($strategy, $added, $replaced);

        $this->doExport();
        $this->validateExportResult();
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
                    'entity'           => 'OroCRM\Bundle\ContactBundle\Entity\Contact',
                    '_widgetContainer' => 'dialog'
                )
            )
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains($strategy, $result->getContent());

        $this->file = $this->getImportTemplate();
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

    protected function doExport()
    {
        $this->client->followRedirects(false);
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_importexport_export_instant',
                array(
                    'processorAlias' => 'orocrm_contact',
                    '_format'        => 'json'
                )
            )
        );

        $data = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertTrue($data['success']);
        $this->assertEquals(1, $data['readsCount']);
        $this->assertEquals(0, $data['errorsCount']);

        $this->client->request(
            'GET',
            $data['url']
        );

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, 200);
        $this->assertResponseContentTypeEquals($result, 'text/csv');
    }

    protected function validateExportResult()
    {
        $importTemplate = $this->getFileContents($this->file);
        $exportedData = $this->getFileContents($this->getExportFile());

        $commonFields = array_intersect($importTemplate[0], $exportedData[0]);

        $importTemplateValues = $this->extractFieldValues($commonFields, $importTemplate);
        $exportedDataValues = $this->extractFieldValues($commonFields, $exportedData);

        $this->assertExportResults($importTemplateValues, $exportedDataValues);
    }

    /**
     * @param array $expected
     * @param array $actual
     */
    protected function assertExportResults(array $expected, array $actual)
    {
        $this->assertCollectionData($expected, $actual, ['Emails 2 Email', 'Emails 3 Email']);
        $this->assertCollectionData($expected, $actual, ['Phones 2 Phone', 'Phones 3 Phone']);
        $this->assertCollectionData($expected, $actual, ['Addresses 2 Street', 'Addresses 3 Street']);
        $this->assertCollectionData($expected, $actual, ['Addresses 2 Zip/postal code', 'Addresses 3 Zip/postal code']);
        $this->assertArrayData($expected, $actual, 'Tags');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param array $expected
     * @param array $actual
     * @param string $key
     */
    protected function assertArrayData(array &$expected, array &$actual, $key)
    {
        $this->assertArrayHasKey($key, $expected);
        $this->assertArrayHasKey($key, $actual);

        $e = $this->stringToArray($expected[$key]);
        $a = $this->stringToArray($actual[$key]);
        sort($e);
        sort($a);

        $this->assertEquals($e, $a);

        unset($expected[$key]);
        unset($actual[$key]);
    }

    protected function stringToArray($string)
    {
        return explode(', ', $string);
    }

    /**
     * Order of elements in collection is not important, except the first (primary) element
     *
     * @param array $expected
     * @param array $actual
     * @param array $keys
     */
    protected function assertCollectionData(array &$expected, array &$actual, array $keys)
    {
        $expectedValues = [];
        $actualValues = [];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $expected);
            $this->assertArrayHasKey($key, $actual);
            $expectedValues[] = $expected[$key];
            $actualValues[] = $actual[$key];
            unset($expected[$key]);
            unset($actual[$key]);
        }

        sort($expectedValues);
        sort($actualValues);

        $this->assertEquals($expectedValues, $actualValues);
    }

    /**
     * @param array $fields
     * @param array $data
     * @return array
     */
    protected function extractFieldValues(array $fields, array $data)
    {
        // ID is changed
        // birthdays have different timestamps
        $skippedFields = ['Id', 'Birthday'];

        $values = [];
        foreach ($fields as $field) {
            if (!in_array($field, $skippedFields)) {
                $key = array_search($field, $data[0]);
                if (false !== $key) {
                    $values[$field] = $data[1][$key];
                }
            }
        }

        return $values;
    }

    /**
     * @return string
     */
    protected function getImportTemplate()
    {
        $result = $this
            ->getContainer()
            ->get('oro_importexport.handler.export')
            ->getExportResult(
                JobExecutor::JOB_EXPORT_TEMPLATE_TO_CSV,
                'orocrm_contact',
                ProcessorRegistry::TYPE_EXPORT_TEMPLATE
            );

        $chains = explode('/', $result['url']);
        return $this
            ->getContainer()
            ->get('oro_importexport.file.file_system_operator')
            ->getTemporaryFile(end($chains))
            ->getRealPath();
    }

    /**
     * @return string
     */
    protected function getExportFile()
    {
        $result = $this
            ->getContainer()
            ->get('oro_importexport.handler.export')
            ->handleExport(
                JobExecutor::JOB_EXPORT_TO_CSV,
                'orocrm_contact',
                ProcessorRegistry::TYPE_EXPORT
            );

        $result = json_decode($result->getContent(), true);
        $chains = explode('/', $result['url']);
        return $this
            ->getContainer()
            ->get('oro_importexport.file.file_system_operator')
            ->getTemporaryFile(end($chains))
            ->getRealPath();
    }

    /**
     * @param string $fileName
     * @return array
     */
    protected function getFileContents($fileName)
    {
        $content = file_get_contents($fileName);
        $content = explode("\n", $content);
        $content = array_filter($content, 'strlen');
        return array_map('str_getcsv', $content);
    }
}
