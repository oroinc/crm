<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Entity;

use OroCRM\Bundle\CallBundle\Entity\Call;

class CallTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new Call();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($expected, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function getSetDataProvider()
    {
        $now = new \DateTime('now');
        $user = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User');
        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact');
        $account = $this->getMockBuilder('OroCRM\Bundle\AccountBundle\Entity\Account');
        $contactPhoneNumber = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\ContactPhone');
        $callStatus = $this->getMockBuilder('OroCRM\Bundle\CallBundle\Entity\CallStatus');

        return array(
            'owner' => array('owner', $user, $user),            
            'relatedContact' => array('contact', $contact, $contact),
            'relatedAccount' => array('account ', $account , $account),
            'subject' => array('subject', 'test', 'test'),
            'phoneNumber' => array('phoneNumber', 'test', 'test'),
            'contactPhoneNumber' => array('contactPhoneNumber', $contactPhoneNumber, $contactPhoneNumber),
            'notes' => array('notes', 'test', 'test'),
            'callDateTime' => array('callDateTime', $now, $now),
            'callStatus' => array('callStatus', $callStatus, $callStatus),
            'duration' => array('duration', 1, 1),
            'direction' => array('direction', true, true),
        );
    }
}
