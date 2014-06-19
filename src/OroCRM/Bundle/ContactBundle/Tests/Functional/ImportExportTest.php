<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional;

use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository as BatchJobRepository;

use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Crawler;

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

        $this->file = $this->getImportTemplate();
    }

    protected function tearDown()
    {
        // clear DB from separate connection
        $batchJobManager = $this->getBatchJobManager();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:JobInstance')->execute();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:JobExecution')->execute();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:StepExecution')->execute();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getBatchJobManager()
    {
        /** @var BatchJobRepository $batchJobRepository */
        $batchJobRepository = $this->client->getKernel()->getContainer()->get('akeneo_batch.job_repository');
        return $batchJobRepository->getJobManager();
    }

    /**
     * @return Crawler
     */
    public function testImportFormAction()
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
        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        return $crawler;
    }

    /**
     * @param Crawler $crawler
     *
     * @depends testImportFormAction
     */
    public function testImportValidateAction(Crawler $crawler)
    {
        $this->assertTrue(file_exists($this->file));

        /** @var Form $form */
        $form = $crawler->selectButton('Submit')->form();

        /** TODO Change after BAP-1813 */
        $form->getFormNode()->setAttribute(
            'action',
            $form->getFormNode()->getAttribute('action') . '&_widgetContainer=dialog'
        );

        $form['oro_importexport_import[file]']->upload($this->file);

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $crawler = $this->client->getCrawler();
        $this->assertEquals(0, $crawler->filter('.import-errors')->count());
    }

    /**
     * @depends testImportValidateAction
     */
    public function testImportProcessAction()
    {
        $this->client->followRedirects(false);
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_importexport_import_process',
                array(
                    'processorAlias' => 'orocrm_contact.add_or_replace',
                    '_format'        => 'json'
                )
            )
        );

        $data = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals(
            array(
                'success'   => true,
                'message'   => 'File was successful imported.',
                'errorsUrl' => null
            ),
            $data
        );
    }

    /**
     * @depends testImportProcessAction
     */
    public function testInstantExport()
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

        return $data['url'];
    }

    /**
     * @param $url
     *
     * @depends testInstantExport
     */
    public function testDownloadExportResultAction($url)
    {
        $this->client->request(
            'GET',
            $url
        );

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, 200);
        $this->assertResponseContentTypeEquals($result, 'text/csv');
    }

    /**
     * @depends testDownloadExportResultAction
     */
    public function testDataValid()
    {
        $data    = $this->getFileContents($this->file);
        $content = $this->getFileContents($this->getExportFile());

        $excludedProperties = [
            'Accounts 1 Account name',
            'Accounts 2 Account name',
        ];

        $this->assertEquals($content[0], $data[0]);

        foreach ($excludedProperties as $excludedProperty) {
            $key = array_search($excludedProperty, $data[0]);
            if (false !== $key) {
                unset($data[0][$key], $data[1][$key], $content[0][$key]);
            }
        }

        $content = array_combine($content[0], $content[1]);
        $data    = array_combine($data[0], $data[1]);

        // @todo: fix date BAP-4560
        unset($data['Birthday'], $content['Birthday'], $data['Id'], $content['Id']);

        $this->assertEquals($data, $content);
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
