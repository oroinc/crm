<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Symfony\Component\DomCrawler\Form;

class ControllersTest extends WebTestCase
{
    use UserUtilityTrait;

    protected function setUp(): void
    {
        $this->initClient(
            array(),
            $this->generateBasicAuthHeader()
        );
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
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_contact_form[firstName]'] = 'Contact_fname';
        $form['oro_contact_form[lastName]'] = 'Contact_lname';
        $form['oro_contact_form[owner]'] = '1';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Contact saved", $crawler->html());
    }

    /**
     * @depend testCreate
     * @return int
     */
    public function testUpdate()
    {
        $response = $this->client->requestGrid(
            'contacts-grid',
            array(
                'contacts-grid[_filter][firstName][value]' => 'Contact_fname',
            )
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $id = $result['id'];
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_contact_update', array('id' => $id))
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_contact_form[firstName]'] = 'Contact_fname_updated';
        $form['oro_contact_form[lastName]'] = 'Contact_lname_updated';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Contact saved", $crawler->html());

        return $id;
    }

    /**
     * @depends testUpdate
     * @param int $id
     */
    public function testView($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_contact_view', array('id' => $id))
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertMatchesRegularExpression(
            "/Contact_fname_updated\s+Contact_lname_updated - Contacts - Customers/",
            $crawler->html()
        );
    }

    /**
     * @depends testUpdate
     * @param int $id
     */
    public function testDelete($id)
    {
        $this->ajaxRequest(
            'DELETE',
            $this->getUrl('oro_api_delete_contact', array('id' => $id))
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_contact_view', array('id' => $id))
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }

    /**
     * @depends testCreate
     */
    public function testMassAction()
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManagerForClass('OroContactBundle:Contact');
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
            array()
        );

        $result = $this->getJsonResponseContent($response, 200);

        $id = array();
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
        $this->assertEquals("5 entities have been deleted successfully", $result['message']);
        $this->assertEquals(5, $result['count']);

        $response = $this->client->requestGrid(
            'contacts-grid',
            array()
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertEmpty($result['data']);
    }
}
