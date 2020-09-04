<?php
declare(strict_types=1);

namespace Oro\Bundle\AccountBundle\Tests\Functional\Controller;

use Oro\Bundle\AccountBundle\Event\CollectAccountWebsiteActivityCustomersEvent;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UIBundle\Tests\Functional\Stub\PlaceholderConfigurationProviderDecorator;

class AccountControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
    }

    public function testIndex(): void
    {
        $this->client->request('GET', $this->getUrl('oro_account_index'));
        $result = $this->client->getResponse();

        static::assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testCreate(): void
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_account_create'));
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_account_form[name]'] = 'Account_name';
        $form['oro_account_form[owner]'] = 1;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        static::assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('Account saved', $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testUpdate(): int
    {
        $response = $this->client->requestGrid(
            'accounts-grid',
            ['accounts-grid[_filter][name][value]' => 'Account_name']
        );

        $result = static::getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $id = $result['id'];
        $crawler = $this->client->request('GET', $this->getUrl('oro_account_update', ['id' => $result['id']]));
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_account_form[name]'] = 'Account_name_update';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        static::assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('Account saved', $crawler->html());

        return (int) $id;
    }

    /**
     * @depends testUpdate
     */
    public function testView($id): void
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_account_view', ['id' => $id]));
        $result = $this->client->getResponse();

        static::assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('Account_name_update - Accounts - Customers', $crawler->html());
    }

    /**
     * @depends testUpdate
     */
    public function testViewWithCustomerDataSuppliedByEventListener($id): void
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

        $crawler = $this->client->request('GET', $this->getUrl('oro_account_view', ['id' => $id]));
        $result = $this->client->getResponse();

        static::assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('Account_name_update - Accounts - Customers', $crawler->html());
        static::assertStringContainsString(
            \trim(static::$container->get('twig')->render($template, ['customers' => $customers])),
            $crawler->html()
        );
    }

    /**
     * @depends testUpdate
     */
    public function testContactWidget($id): void
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_account_widget_contacts_info',
                ['id' => $id, '_widgetContainer' => 'dialog']
            )
        );
        //just verify method OK
        $result = $this->client->getResponse();
        static::assertHtmlResponseStatusCodeEquals($result, 200);
    }
}
