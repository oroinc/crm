<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\EmbeddedFormBundle\Tests\Selenium\Pages\EmbeddedForms;

/**
 * Class CreateEmbeddedFormTest
 *
 * @package OroCRM\Bundle\ContactUsBundle\Tests\Selenium
 */
class CreateLeadTest extends Selenium2TestCase
{
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
            ->assertTitle('Embedded Forms - Integrations - System');

        return $title;
    }

    /**
     * @depends testCreateEmbeddedForm
     * @param $title
     * @return string
     */
    public function testUpdateEmbeddedForm($title)
    {
        $newTitle = 'Update_' . $title;

        $login = $this->login();
        /** @var EmbeddedForms $login */
        $login->openEmbeddedForms('Oro\Bundle\EmbeddedFormBundle')
            ->filterBy('Title', $title)
            ->open(array($title))
            ->edit()
            ->assertTitle($title . ' - Edit - Embedded Forms - Integrations - System')
            ->setTitle($newTitle)
            ->save()
            ->assertMessage('Form has been saved successfully');

        return $newTitle;
    }

    /**
     * @depends testUpdateEmbeddedForm
     * @param $title
     */
    public function testDeleteEmbeddedForm($title)
    {
        $login = $this->login();
        /** @var EmbeddedForms $login */
        $login->openEmbeddedForms('Oro\Bundle\EmbeddedFormBundle')
            ->filterBy('Title', $title)
            ->open(array($title))
            ->delete()
            ->assertTitle('Embedded Forms - Integrations - System')
            ->assertMessage('Embedded Form deleted')
            ->filterBy('Title', $title)
            ->assertNoDataMessage('No embedded form was found to match your search');
    }
}
