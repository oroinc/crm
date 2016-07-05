<?php

namespace OroCRM\Bundle\CallBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\CallBundle\Tests\Selenium\Pages\Calls;

/**
 * Class CreateCallTest
 *
 * @package OroCRM\Bundle\CallBundle\Tests\Selenium
 * {@inheritdoc}
 */
class CreateCallTest extends Selenium2TestCase
{
    /**
     * Test new Call creation functionality
     * @return string
     */
    public function testCreateCall()
    {
        $callSubject = 'Call_'.mt_rand(100, 400);
        $phoneNumber = mt_rand(100, 999).'-'.mt_rand(100, 999).'-'.mt_rand(1000, 9999);

        $login = $this->login();
        /** @var Calls $login */
        $login->openCalls('OroCRM\Bundle\CallBundle')
            ->assertTitle('All - Calls - Activities')
            ->add()
            ->assertTitle('Log Call - Calls - Activities')
            ->setCallSubject($callSubject)
            ->setPhoneNumber($phoneNumber)
            ->save()
            ->assertMessage('Call saved')
            ->assertTitle($callSubject.' - Calls - Activities')
            ->close();

        return $callSubject;
    }

    /**
     * Test update existing Call
     * @depends testCreateCall
     * @param $callSubject
     * @return string
     */
    public function testUpdateCall($callSubject)
    {
        $newCallSubject = 'Update_' . $callSubject;

        $login = $this->login();
        /** @var Calls $login */
        $login->openCalls('OroCRM\Bundle\CallBundle')
            ->filterBy('Subject', $callSubject)
            ->open(array($callSubject))
            ->assertTitle($callSubject . ' - Calls - Activities')
            ->edit()
            ->assertTitle($callSubject . ' - Edit - Calls - Activities')
            ->setCallSubject($newCallSubject)
            ->save()
            ->assertMessage('Call saved')
            ->assertTitle($newCallSubject.' - Calls - Activities')
            ->close();

        return $newCallSubject;
    }

    /**
     * Test delete existing Call
     * @depends testUpdateCall
     * @param $newCallSubject
     */
    public function testDeleteCall($newCallSubject)
    {
        $login = $this->login();
        /** @var Calls $login */
        $login->openCalls('OroCRM\Bundle\CallBundle')
            ->filterBy('Subject', $newCallSubject)
            ->delete(array($newCallSubject))
            ->assertMessage('Item deleted')
            ->assertTitle('All - Calls - Activities');

        $login->openCalls('OroCRM\Bundle\CallBundle')
            ->assertNoDataMessage('No records found');
    }
}
