<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional;

use Symfony\Component\DomCrawler\Form;

use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ControllersTest extends WebTestCase
{
    use UserUtilityTrait;

    protected function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->client->useHashNavigation(true);
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('orocrm_contact_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('orocrm_contact_create'));
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_contact_form[firstName]'] = 'Contact_fname';
        $form['orocrm_contact_form[lastName]'] = 'Contact_lname';
        $form['orocrm_contact_form[owner]'] = '1';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Contact saved", $crawler->html());
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
            $this->getUrl('orocrm_contact_update', array('id' => $id))
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_contact_form[firstName]'] = 'Contact_fname_updated';
        $form['orocrm_contact_form[lastName]'] = 'Contact_lname_updated';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Contact saved", $crawler->html());

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
            $this->getUrl('orocrm_contact_view', array('id' => $id))
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertRegExp("/Contact_fname_updated\s+Contact_lname_updated - Contacts - Customers/", $crawler->html());
    }

    /**
     * @depends testUpdate
     * @param int $id
     */
    public function testDelete($id)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_contact', array('id' => $id))
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_contact_view', array('id' => $id))
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }

    /**
     * @depends testCreate
     */
    public function testMassAction()
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManagerForClass('OroCRMContactBundle:Contact');
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
        $this->client->request(
            'DELETE',
            $this->getUrl(
                'oro_datagrid_mass_action',
                array('gridName' => 'contacts-grid', 'actionName' => 'delete', 'values' => $id, 'inset' => 1)
            )
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertTrue($result['successful']);
        $this->assertEquals("5 entities were deleted", $result['message']);
        $this->assertEquals(5, $result['count']);

        $response = $this->client->requestGrid(
            'contacts-grid',
            array()
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertEmpty($result['data']);
    }
}
