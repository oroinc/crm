<?php
declare(strict_types=1);

namespace Oro\Bundle\AccountBundle\Tests\Functional;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class ControllersTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['@OroAccountBundle/Tests/Functional/DataFixtures/accounts_data.yml']);
    }

    public function testContactUpdateGrid()
    {
        $accountRepository = self::getContainer()->get('doctrine')->getRepository(Account::class);
        $accountId = $accountRepository->findOneBy(['name' => 'Account 1'])->getId();

        $this->client->request('GET', $this->getUrl('oro_account_view', ['id' => $accountId]));

        $this->client->followRedirects(true);
        $response = $this->client->requestGrid(
            'account-contacts-update-grid',
            ['account-contacts-update-grid[account]' => $accountId]
        );

        $result = self::getJsonResponseContent($response, 200);

        self::assertCount(2, $result['data'], \var_export($result['data'], true));
        self::assertEquals(2, $result['options']['totalRecords']);
    }

    public function testDelete()
    {
        $accountRepository = self::getContainer()->get('doctrine')->getRepository(Account::class);
        $accountId = $accountRepository->findOneBy(['name' => 'Account 1'])->getId();

        $this->client->request('GET', $this->getUrl('oro_account_view', ['id' => $accountId]));

        $this->client->followRedirects(true);
        $this->ajaxRequest('DELETE', $this->getUrl('oro_api_delete_account', ['id' => $accountId]));

        $result = $this->client->getResponse();
        self::assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request('GET', $this->getUrl('oro_account_view', ['id' => $accountId]));

        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 404);
    }
}
