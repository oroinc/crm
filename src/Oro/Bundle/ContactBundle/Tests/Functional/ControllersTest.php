<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;

class ControllersTest extends WebTestCase
{
    use UserUtilityTrait;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_contact_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_contact_create'));
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_contact_form[firstName]'] = 'Contact_fname';
        $form['oro_contact_form[lastName]'] = 'Contact_lname';
        $form['oro_contact_form[owner]'] = '1';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('Contact saved', $crawler->html());
    }

    /**
     * @depend testCreate
     */
    public function testUpdate(): int
    {
        $response = $this->client->requestGrid(
            'contacts-grid',
            [
                'contacts-grid[_filter][firstName][value]' => 'Contact_fname',
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $id = $result['id'];
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_contact_update', ['id' => $id])
        );
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_contact_form[firstName]'] = 'Contact_fname_updated';
        $form['oro_contact_form[lastName]'] = 'Contact_lname_updated';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('Contact saved', $crawler->html());

        return $id;
    }

    /**
     * @depends testUpdate
     */
    public function testView(int $id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_contact_view', ['id' => $id])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertMatchesRegularExpression(
            '/Contact_fname_updated\s+Contact_lname_updated - Contacts - Customers/',
            $crawler->html()
        );
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(int $id)
    {
        $this->ajaxRequest(
            'DELETE',
            $this->getUrl('oro_api_delete_contact', ['id' => $id])
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_contact_view', ['id' => $id])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }

    /**
     * @depends testCreate
     */
    public function testMassAction()
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManagerForClass(Contact::class);
        $owner = $this->getFirstUser($entityManager);

        for ($i = 1; $i <= 5; $i++) {
            $contact = new Contact();
            $contact->setFirstName('Contact_fname' . $this->generateRandomString(5))
                ->setLastName('Contact_lname' . $this->generateRandomString(5))
                ->setOwner($owner);
            $entityManager->persist($contact);
        }
        $entityManager->flush();

        $response = $this->client->requestGrid(
            'contacts-grid',
            []
        );

        $result = $this->getJsonResponseContent($response, 200);

        $id = [];
        foreach ($result['data'] as $value) {
            $id[] = $value['id'];
        }
        $id = implode(',', $id);
        $this->ajaxRequest(
            'DELETE',
            $this->getUrl(
                'oro_datagrid_mass_action',
                [
                    'gridName' => 'contacts-grid',
                    'actionName' => 'delete',
                    'values' => $id,
                    'inset' => 1
                ]
            )
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertTrue($result['successful']);
        $this->assertEquals('5 entities have been deleted successfully', $result['message']);
        $this->assertEquals(5, $result['count']);

        $response = $this->client->requestGrid(
            'contacts-grid',
            []
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertEmpty($result['data']);
    }
}
