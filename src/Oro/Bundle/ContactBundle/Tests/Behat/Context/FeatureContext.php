<?php

namespace Oro\Bundle\ContactBundle\Tests\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Oro\Bundle\GaufretteBundle\FileManager;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;

/**
 * Behat feature content for Contact bundle.
 */
class FeatureContext extends OroFeatureContext implements OroPageObjectAware
{
    use PageObjectDictionary;

    /**
     * Assert that value of given field is a primary.
     * In frontend primary value is marked as bold.
     * Also primary value is value that showing in grid
     * Example: And Phone "+1 415-731-9375" should be primary
     * Example: And email "charlie@gmail.com" should be primary
     *
     * @Then /^(?P<field>[^"]+) "(?P<value>[^"]+)" should be primary$/
     */
    public function fieldValueShouldBePrimary($field, $value)
    {
        $labelSelector = sprintf(
            "label:contains('%s')",
            ucfirst((new InflectorFactory())->build()->pluralize($field))
        );
        /** @var NodeElement $label */
        $label = $this->getSession()->getPage()->find('css', $labelSelector);
        self::assertNotNull($label, sprintf('Label "%s" not found', $field));
        $contactElements = $label->getParent()->findAll('css', '.contact-collection-element');

        /** @var NodeElement $contactElement */
        foreach ($contactElements as $contactElement) {
            if (false !== stripos($contactElement->getText(), $value)) {
                self::assertTrue(
                    $contactElement->hasClass('primary'),
                    sprintf('Value "%s" was found but it is not primary', $value)
                );

                return;
            }
        }

        self::fail(sprintf('Value "%s" in "%s" field not found', $value, $field));
    }

    /**
     * Assert that entity view page has default avatar (info-user.png)
     *
     * @Then avatar should be default avatar
     */
    public function avatarShouldBeDefaultAvatar()
    {
        $icon = $this->getSession()->getPage()->find('css', 'div.page-title div.page-title__icon');

        self::assertNull($icon->find('css', 'img'), 'Avatar is not default avatar');
    }

    /**
     * Assert that entity view page has default avatar (info-user.png)
     *
     * @Then avatar should not be default avatar
     */
    public function avatarShouldNotBeDefaultAvatar()
    {
        $img = $this->getSession()->getPage()->find('css', 'div.page-title div.page-title__icon img');

        self::assertFalse(stripos($img->getAttribute('src'), 'info-user.png'), 'Avatar is not expected image');
    }

    /**
     * Assert that two accounts sets at view entity page
     * Example: And Warner Brothers and Columbia Pictures should be set as accounts
     *
     * @Then /^(?P<acc1>[^"]+) and (?P<acc2>[^"]+) should be set as accounts$/
     */
    public function assertAccountsNames($acc1, $acc2)
    {
        $labelSelector = sprintf("label:contains('%s')", 'Accounts');
        /** @var NodeElement $label */
        $label = $this->getSession()->getPage()->find('css', $labelSelector);
        $accounts = $label->getParent()->findAll('css', '.control-label a');
        $accounts = array_map(function (NodeElement $a) {
            return $a->getText();
        }, $accounts);

        foreach ([$acc1, $acc2] as $acc) {
            self::assertTrue(
                in_array($acc, $accounts, true),
                sprintf('Value "%s" not found in "%s" accounts', $acc, implode(', ', $accounts))
            );
        }
    }

    /**
     * Assert social links
     * Example: And should see next social links:
     *            | Twitter    | https://twitter.com/charliesheen                  |
     *            | Facebook   | https://www.facebook.com/CharlieSheen             |
     *            | Google+    | https://profiles.google.com/111536551725236448567 |
     *            | LinkedIn   | http://www.linkedin.com/in/charlie-sheen-74755931 |
     *
     * @Then should see next social links:
     */
    public function shouldSeeNextSocialLinks(TableNode $table)
    {
        $labelSelector = sprintf("label:contains('%s')", 'Social');
        /** @var NodeElement $label */
        $label = $this->getSession()->getPage()->find('css', $labelSelector);
        $links = $label->getParent()->findAll('css', 'ul.social-list li a');

        $socialNetworks = [];

        /** @var NodeElement $link */
        foreach ($links as $link) {
            $socialNetworks[$link->getAttribute('title')] = trim($link->getAttribute('href'));
        }

        foreach ($table->getRows() as [$networkName, $networkLink]) {
            self::assertArrayHasKey(
                $networkName,
                $socialNetworks,
                sprintf('%s not found in social networks', $networkName)
            );
            self::assertEquals(
                $networkLink,
                $socialNetworks[$networkName],
                sprintf('%s expect to be "%s" but got "%s"', $networkName, $networkLink, $socialNetworks[$networkName])
            );
        }
    }

    /**
     * @param int|string $count
     * @return int
     */
    protected function getCount($count)
    {
        switch (trim($count)) {
            case '':
                return 1;
            case 'one':
                return 1;
            case 'two':
                return 2;
            default:
                return (int) $count;
        }
    }

    /**
     * Example: Given I copy contact fixture "charlie-sheen.jpg" to import upload dir
     *
     * @Given /^I copy contact fixture "(?P<filename>(?:[^"]|\\")*)" to import upload dir$/
     */
    public function copyContactFixtureFileToImportFilesDir(string $filename): void
    {
        $filename = $this->fixStepArgument($filename);
        $imagePath = sprintf('%s/../Features/Fixtures/%s', __DIR__, $filename);

        /** @var FileManager $fileManager */
        $fileManager = $this->getAppContainer()->get('oro_attachment.importexport.file_manager.import_files');
        $fileManager->writeFileToStorage($imagePath, $filename);
    }
}
