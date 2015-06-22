<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\EmbeddedFormBundle\Tests\Selenium\Pages\EmbeddedForms;
use OroCRM\Bundle\ContactUsBundle\Tests\Selenium\Pages\ContactRequests;

/**
 * Class ManageEmbeddedFormTest
 *
 * @package OroCRM\Bundle\ContactUsBundle\Tests\Selenium
 */
class ManageEmbeddedFormTest extends Selenium2TestCase
{

    public function setUp()
    {
        $this->markTestSkipped('Due to bug BAP-4693');
        /*
        Also when bug will be fixed, need to correct EmbeddedForm object methods
        */
    }
    /**
     * @return string
     */
    public function testCreateEmbeddedForm()
    {
        $title = 'Form_'.mt_rand(10, 99);

        $login = $this->login();
        /** @var EmbeddedForms $login */
        $login->openEmbeddedForms('Oro\Bundle\EmbeddedFormBundle')
            ->add()
            ->setTitle($title)
            ->save()
            ->assertMessage('Form has been saved successfully')
            ->assertTitle($title . ' - Embedded Forms - Integrations - System')
            ->checkPreview()
            ->toGrid()
            ->assertTitle('All - Embedded Forms - Integrations - System');

        return $title;
    }

    /**
     * @param $title
     * @return string
     */
    public function testSendEmbeddedForm($title = 'asdfasdf')
    {
        $email = 'Email_'.mt_rand(10, 99).'@mail.com';

        $login = $this->login();
        /** @var EmbeddedForms $login */
        $login->openEmbeddedForms('Oro\Bundle\EmbeddedFormBundle')
            ->filterBy('Title', $title)
            ->open(array($title))
            ->setFirstName('First name_'.mt_rand(10, 99))
            ->setLastName('Last name_'.mt_rand(10, 99))
            ->setEmail($email)
            ->setComment('Test comment message '.mt_rand(10, 99))
            ->submitForm();

        return $email;
    }

    /**
     * @depends testSendEmbeddedForm
     * @param $email
     */
    public function testEmbeddedFormRequestGridAvailability($email)
    {
        $login = $this->login();
        /** @var ContactRequests $login */
        $data = $login->openContactRequests('OroCRM\Bundle\ContactUsBundle')
            ->filterBy('Email', $email)
            ->getAllData();
        $this->assertEquals($email, $data[0]['EMAIL']);
    }
}
