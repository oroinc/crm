<?php
declare(strict_types=1);

namespace Oro\Bundle\AccountBundle\Tests\Functional\Controller;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Event\CollectAccountWebsiteActivityCustomersEvent;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UIBundle\Tests\Functional\Stub\PlaceholderConfigurationProviderDecorator;

class AccountControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['@OroAccountBundle/Tests/Functional/DataFixtures/accounts_data.yml']);
    }

    public function testViewWithCustomerDataSuppliedByEventListener(): void
    {
        $customers = ['one', 'two'];
        $template = 'testCustomersWebActivity.html.twig';

        $listener = static function (CollectAccountWebsiteActivityCustomersEvent $event) use ($customers) {
            $event->setCustomers($customers);
        };
        $this->client->getContainer()->get('event_dispatcher')->addListener(
            CollectAccountWebsiteActivityCustomersEvent::class,
            $listener
        );

        /** @var PlaceholderConfigurationProviderDecorator $placeholderConfigProvider */
        $placeholderConfigProvider = $this->client->getContainer()->get(
            'test.oro_ui.placeholder.configuration.provider'
        );
        $placeholderConfigProvider->addPlaceholderItem('oro_website_activity', __FUNCTION__, ['template' => $template]);

        $this->client->disableReboot();

        $accountRepository = self::getContainer()->get('doctrine')->getRepository(Account::class);
        $accountId = $accountRepository->findOneBy(['name' => 'Account 1'])->getId();

        $crawler = $this->client->request('GET', $this->getUrl('oro_account_view', ['id' => $accountId]));
        $result = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('Account 1 - Accounts - Customers', $crawler->html());
        self::assertStringContainsString(
            trim(self::getContainer()->get('twig')->render($template, ['customers' => $customers])),
            $crawler->html()
        );
    }
}
