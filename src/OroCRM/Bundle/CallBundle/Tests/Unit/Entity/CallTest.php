<?php

namespace OroCRM\Bundle\CallBundle\Tests\Unit\Entity;

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
        $user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $contact = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\Contact');
        $account = $this->getMock('OroCRM\Bundle\AccountBundle\Entity\Account');
        $contactPhoneNumber = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\ContactPhone');
        $callStatus = $this->getMock('OroCRM\Bundle\CallBundle\Entity\CallStatus', array(), array(), '', false);
        $callDirection = $this->getMock('OroCRM\Bundle\CallBundle\Entity\CallDirection', array(), array(), '', false);

        return array(
            'owner' => array('owner', $user, $user),
            'relatedContact' => array('relatedContact', $contact, $contact),
            'relatedAccount' => array('relatedAccount', $account , $account),
            'subject' => array('subject', 'test', 'test'),
            'phoneNumber' => array('phoneNumber', 'test', 'test'),
            'contactPhoneNumber' => array('contactPhoneNumber', $contactPhoneNumber, $contactPhoneNumber),
            'notes' => array('notes', 'test', 'test'),
            'callDateTime' => array('callDateTime', $now, $now),
            'callStatus' => array('callStatus', $callStatus, $callStatus),
            'duration' => array('duration', 1, 1),
            'direction' => array('direction', $callDirection, $callDirection),
        );
    }
}
