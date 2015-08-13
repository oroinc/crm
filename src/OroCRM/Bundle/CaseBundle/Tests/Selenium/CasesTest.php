<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\CaseBundle\Tests\Selenium\Pages\Cases;

/**
 * Class CasesTest
 *
 * @package OroCRM\Bundle\CaseBundle\Tests\Selenium
 */
class CasesTest extends Selenium2TestCase
{
    /**
     * @return string
     */
    public function testCreate()
    {
        $subject = 'Case_' . mt_rand();

        $login = $this->login();
        /** @var Cases $login */
        $login->openCases('OroCRM\Bundle\CaseBundle')
            ->assertTitle('All - Cases - Activities')
            ->add()
            ->assertTitle('Create Case - Cases - Activities')
            ->setSubject($subject)
            ->setDescription($subject)
            ->save()
            ->assertMessage('Case saved')
            ->toGrid()
            ->assertTitle('All - Cases - Activities');

        return $subject;
    }

    /**
     * @depends testCreate
     * @param $subject
     * @return string
     */
    public function testUpdate($subject)
    {
        $newSubject = 'Update_' . $subject;

        $login = $this->login();
        /** @var Cases $login */
        $login->openCases('OroCRM\Bundle\CaseBundle')
            ->filterBy('Subject', $subject)
            ->open(array($subject))
            ->assertTitle("{$subject} - Cases - Activities")
            ->edit()
            ->assertTitle("{$subject} - Edit - Cases - Activities")
            ->setSubject($newSubject)
            ->save()
            ->assertMessage('Case saved')
            ->toGrid()
            ->assertTitle('All - Cases - Activities');

        return $newSubject;
    }

    /**
     * @depends testUpdate
     * @param $subject
     * @param $status
     * @dataProvider statusProvider
     */
    public function testManage($status, $subject)
    {
        $login = $this->login();
        /** @var Cases $login */
        $login->openCases('OroCRM\Bundle\CaseBundle')
            ->filterBy('Subject', $subject)
            ->open(array($subject))
            ->edit()
            ->assertTitle($subject . ' - Edit - Cases - Activities')
            ->setStatus($status['status']) //Open, Resolved, Closed
            ->save()
            ->assertMessage('Case saved')
            ->toGrid()
            ->assertTitle('All - Cases - Activities');

        $data = $login->openCases('OroCRM\Bundle\CaseBundle')
            ->filterBy('Subject', $subject)
            ->getAllData();
        static::assertEquals($data[0]['STATUS'], $status['status']);
        if ($status['closed'] === "") {
            static::assertEquals($data[0]['CLOSED ON'], $status['closed']);
        } else {
            static::assertNotEquals($data[0]['CLOSED ON'], "");
        }
    }

    public function statusProvider()
    {
        return array(
            array('In Progress' => array('status' => 'In Progress', 'closed' => '')),
            array('Resolved' => array('status' => 'Resolved',  'closed'  => '')),
            array('Closed' => array('status' => 'Closed',  'closed' => date('c')))
        );
    }

    /**
     * @depends testUpdate
     * @param $subject
     */
    public function testDelete($subject)
    {
        $login = $this->login();
        /** @var Cases $login */
        $login->openCases('OroCRM\Bundle\CaseBundle')
            ->filterBy('Subject', $subject)
            ->open(array($subject))
            ->delete()
            ->assertMessage('Case deleted')
            ->assertTitle('All - Cases - Activities');
        /** @var Cases $login */
        $login->openCases('OroCRM\Bundle\CaseBundle');
        if ($login->getRowsCount() > 0) {
            $login->filterBy('Subject', $subject)
                ->assertNoDataMessage('No entity was found to match your search');
        }
    }
}
