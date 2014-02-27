<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional;

use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository as BatchJobRepository;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @outputBuffering enabled
 * @db_isolation
 * @db_reindex
 */
class ImportExportTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    protected function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateBasicHeader());
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
            $this->client->generate(
                'oro_importexport_import_form',
                array(
                    'entity' => 'OroCRM\Bundle\ContactBundle\Entity\Contact',
                    '_widgetContainer' => 'dialog'
                )
            )
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');

        return $crawler;
    }

    /**
     * @param Crawler $crawler
     *
     * @depends testImportFormAction
     */
    public function testImportValidateAction($crawler)
    {
        $path = $this->client
            ->getKernel()
            ->locateResource('@OroCRMContactBundle/Resources/public/import/contacts_sample.csv');
        $this->assertTrue(file_exists($path));

        /** @var Form $form */
        $form = $crawler->selectButton('Submit')->form();

        /** TODO Change after BAP-1813 */
        $form->getFormNode()->setAttribute(
            'action',
            $form->getFormNode()->getAttribute('action') . '&_widgetContainer=dialog'
        );

        $form['oro_importexport_import[file]']->upload($path);

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');

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
            $this->client->generate(
                'oro_importexport_import_process',
                array(
                    'processorAlias' => 'orocrm_contact.add_or_replace',
                    '_format' =>'json'
                )
            )
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $data = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEquals(
            array(
                'success' => true,
                'message' => 'File was successful imported.',
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
            $this->client->generate(
                'oro_importexport_export_instant',
                array(
                    'processorAlias' => 'orocrm_contact',
                    '_format' =>'json'
                )
            )
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $data = ToolsAPI::jsonToArray($result->getContent());
        $this->assertTrue($data['success']);
        $this->assertEquals(2, $data['readsCount']);
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
        ToolsAPI::assertJsonResponse($result, 200, 'text/csv');

        // Enable the output buffer
        ob_start();
        // Send the response to the output buffer
        $result->sendContent();
        // Get the contents of the output buffer
        $content = ob_get_contents();
        // Clean the output buffer and end it
        ob_end_clean();

        $content = explode("\n", $content);
        $content = array_filter($content, 'strlen');
        $content = array_map('str_getcsv', $content);
        $path = $this->client
            ->getKernel()
            ->locateResource('@OroCRMContactBundle/Resources/public/import/contacts_sample.csv');

        $data = file_get_contents($path);
        $data = explode("\n", $data);
        $data = array_filter($data, 'strlen');
        $data = array_map('str_getcsv', $data);

        // compare header
        for ($column = 0; $column < count($data[0]); $column++) {
            //skip account
            if (strpos($column, 'Account') != 0) {
                $this->assertTrue(in_array($data[0][$column], $content[0]), $data[0][$column]);
            }
        }

        $headerData = $data[0];
        $headerContent = $content[0];

        for ($row = 1; $row < count($data); $row++) {
            $data[$row] = array_combine($headerData, array_values($data[$row]));
            $content[$row] = array_combine($headerContent, array_values($content[$row]));

            //prepare data
            $data[$row]['Facebook'] = "https://www.facebook.com/" . $data[$row]['Facebook'];
            $data[$row]['Twitter'] = "https://twitter.com/" . $data[$row]['Twitter'];
            $data[$row]['Owner Username'] = 'admin';
            $data[$row]['Owner'] = 'John Doe';
            $data[$row]['Assigned To Username'] = '';
            $data[$row]['Assigned To'] = '';
            $data[$row]['Group 1'] = 'Sales Group';
            $data[$row]['Group 2'] = 'Marketing Group';

            foreach ($headerData as $column) {
                if ($column != 'ID' && strlen($data[$row][$column]) > 0 && strpos($column, 'Account') != 0) {
                    $this->assertEquals($data[$row][$column], $content[$row][$column], $column);
                }
            }
        }
    }
}
