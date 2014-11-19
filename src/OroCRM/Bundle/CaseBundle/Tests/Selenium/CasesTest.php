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
            ->assertTitle('Cases - Activities')
            ->add()
            ->assertTitle('Create Case - Cases - Activities')
            ->setSubject($subject)
            ->setDescription($subject)
            ->save()
            ->assertMessage('Case saved')
            ->toGrid()
            ->assertTitle('Cases - Activities');

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
            ->assertTitle('Cases - Activities');

        return $newSubject;
    }

    /**
     * @depends testUpdate
     * @param $subject
     */
    public function testWorkflow($subject)
    {
        $login = $this->login();
        /** @var Tasks $login */
        $login->openTasks('OroCRM\Bundle\CaseBundle')
            ->filterBy('Subject', $subject)
            ->open(array($subject))
            ->process(array('Start progress' => 'In progress', 'Resolve' => null, 'Close' => null, 'Reopen' => null))
            ->process(array('Start progress' => null, 'Stop progress' => null, 'Close' => null));
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
            ->assertTitle('Cases - Activities')
            ->assertMessage('Case deleted');
        /** @var Cases $login */
        $login->openCases('OroCRM\Bundle\CaseBundle');
        if ($login->getRowsCount() > 0) {
            $login->filterBy('Subject', $subject)
                ->assertNoDataMessage('No entity was found to match your search');
        }
    }
}
