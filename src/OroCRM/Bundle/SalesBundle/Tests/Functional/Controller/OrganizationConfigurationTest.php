<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Controller;

use Oro\Component\PhpUtils\ArrayUtil;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class OrganizationConfigurationTest extends WebTestCase
{
    /** @var ConfigManager */
    protected $configManager;

    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $this->configManager = $this->getContainer()->get('oro_config.organization');
    }

    protected function tearDown()
    {
        $this->configManager->reset('oro_crm_sales.default_opportunity_probabilities');
        $this->configManager->flush();

        parent::tearDown();
    }

    public function testConfig()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oropro_organization_config',
                ['activeGroup' => 'platform', 'activeSubGroup' => 'opportunity_configuration', 'id' => 1]
            )
        );

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        $form = $crawler->selectButton('Save settings')->form();
        $phpValues = $form->getPhpValues();

        $this->assertFalse(
            array_key_exists(
                'value',
                $phpValues['opportunity_configuration']['oro_crm_sales___default_opportunity_probabilities']
            )
        );

        $token = $this->getContainer()
            ->get('security.csrf.token_manager')
            ->getToken('opportunity_configuration')
            ->getValue();

        $formData = ArrayUtil::arrayMergeRecursiveDistinct(
            $phpValues,
            [
                'opportunity_configuration' => [
                    'oro_crm_sales___default_opportunity_probabilities' => [
                        'use_parent_scope_value' => false,
                        'value' => $this->getTestValues(),
                    ],
                    '_token' => $token,
                ],
            ]
        );

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formData);

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        $form = $crawler->selectButton('Save settings')->form();
        $newValues = $form->getPhpValues();

        $this->assertSame(
            $newValues['opportunity_configuration']['oro_crm_sales___default_opportunity_probabilities']['value'],
            $this->getTestValues()
        );
    }

    private function getTestValues()
    {
        return [
            'identification_alignment' => '5',
            'needs_analysis' => '10',
            'solution_development' => '60',
            'negotiation' => '70',
            'in_progress' => '20'
        ];
    }
}
