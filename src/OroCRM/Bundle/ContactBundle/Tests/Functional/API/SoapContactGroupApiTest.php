<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @group soap
 */
class SoapContactGroupApiTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(array(), $this->generateWsseAuthHeader());
        $this->initSoapClient();
    }

    /**
     * @return array
     */
    public function testCreateContactGroup()
    {
        //$this->markTestIncomplete('Verify WSDL scheme');

        $request = array(
            "label" => 'Group name_' . mt_rand(),
            "owner" => '1'
        );
        $result = $this->soapClient->createContactGroup($request);
        $this->assertTrue($result);

        return $request;
    }

    /**
     * @param $request
     * @depends testCreateContactGroup
     * @return array
     */
    public function testGetContactGroups($request)
    {
        $groups = $this->soapClient->getContactGroups(1, 1000);
        $groups = $this->valueToArray($groups);
        $groupLabel = $request['label'];
        $group = array_filter(
            $groups['item'],
            function ($a) use ($groupLabel) {
                return $a['label'] == $groupLabel;
            }
        );
        $this->assertNotEmpty($group);

        return reset($group);
    }

    /**
     * @param $request
     * @param $group
     * @depends testCreateContactGroup
     * @depends testGetContactGroups
     */
    public function testUpdateContact($request, $group)
    {
        $request['label'] .= '_Updated';
        $result = $this->soapClient->updateContactGroup($group['id'], $request);
        $this->assertTrue($result);

        $group = $this->soapClient->getContactGroup($group['id']);
        $group = $this->valueToArray($group);
        $this->assertEquals($request['label'], $group['label']);
    }

    /**
     * @param $group
     * @depends testGetContactGroups
     * @throws \Exception|\SoapFault
     */
    public function testDeleteContactGroup($group)
    {
        $result = $this->soapClient->deleteContactGroup($group['id']);
        $this->assertTrue($result);

        $this->setExpectedException('\SoapFault', 'Record with ID "' . $group['id'] . '" can not be found');

        $this->soapClient->getContactGroup($group['id']);
    }
}
