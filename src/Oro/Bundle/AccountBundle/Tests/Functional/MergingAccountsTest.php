<?php

namespace Oro\Bundle\AccountBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MergingAccountsTest extends WebTestCase
{
    private const GRID_OF_ACCOUNTS = 'accounts-grid';

    protected function setUp(): void
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->loadFixtures(
            [
                '@OroAccountBundle/Tests/Functional/DataFixtures/accounts_data.yml'
            ]
        );
    }

    public function testMergingAccounts()
    {
        $response = $this->client->requestGrid(self::GRID_OF_ACCOUNTS, [], true);
        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);

        $ids = array_map(
            function (array $value) {
                return $value['id'];
            },
            $result['data']
        );

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->getUrl(
                'oro_entity_merge_massaction',
                [
                    'gridName' => self::GRID_OF_ACCOUNTS,
                    'actionName' => 'merge',
                    'inset' => 1,
                    'values' => implode(',', $ids)
                ]
            )
        );

        $this->client->followRedirects(true);

        $form = $crawler->selectButton('Merge')
            ->form();

        $crawler = $this->client->submit($form);

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        static::assertStringContainsString('Entities were successfully merged', $crawler->html());

        $accounts = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroAccountBundle:Account')
            ->findAll();

        $this->assertCount(1, $accounts);
        $this->assertCount(2, $accounts[0]->getContacts());
    }
}
