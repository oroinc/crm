<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\EmbeddedFormBundle\Tests\Selenium\Pages\EmbeddedForms;

/**
 * Class CreateEmbeddedFormTest
 *
 * @package OroCRM\Bundle\ContactUsBundle\Tests\Selenium
 */
class CreateEmbeddedFormTest extends Selenium2TestCase
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
            ->assertTitle('All - Embedded Forms - Integrations - System')
            ->add()
            ->assertTitle('Create Embedded Form - Embedded Forms - Integrations - System')
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
            ->assertTitle("{$title} - Embedded Forms - Integrations - System")
            ->edit()
            ->assertTitle("{$title} - Edit - Embedded Forms - Integrations - System")
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
            ->assertMessage('Embedded Form deleted');

        /** @var EmbeddedForms $login */
        $login->openEmbeddedForms('Oro\Bundle\EmbeddedFormBundle');
        if ($login->getRowsCount() > 0) {
            $login->filterBy('Title', $title)
                ->assertNoDataMessage('No embedded form was found to match your search');
        }
    }
}
