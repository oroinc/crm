<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\RawMinkContext;
use Doctrine\Common\Inflector\Inflector;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroElementFactoryAware;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\ElementFactoryDictionary;

class FeatureContext extends RawMinkContext implements OroElementFactoryAware
{
    use ElementFactoryDictionary;

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
        $labelSelector = sprintf("label:contains('%s')", ucfirst(Inflector::pluralize($field)));
        /** @var NodeElement $label */
        $label = $this->getSession()->getPage()->find('css', $labelSelector);
        $contactElements = $label->getParent()->findAll('css', '.contact-collection-element');

        /** @var NodeElement $contactElement */
        foreach ($contactElements as $contactElement) {
            if (false !== strpos($contactElement->getText(), $value)) {
                if ($contactElement->hasClass('primary')) {
                    return;
                } else {
                    throw new ExpectationException(
                        sprintf('Value "%s" was found but it is not primary', $value),
                        $this->getSession()->getDriver()
                    );
                }
            }
        }

        throw new ExpectationException(
            sprintf('Value "%s" in "%s" field not found', $value, $field),
            $this->getSession()->getDriver()
        );
    }

    /**
     * Click edit icon (pencil) into address at entity view page
     * Example: And click edit LOS ANGELES address
     *
     * @Given /^click edit (?P<address>[^"]+) address$/
     */
    public function clickEditAddress($address)
    {
        $addresses = $this->getSession()->getPage()->findAll('css', 'div.map-address-list .map-item');

        /** @var NodeElement $actualAddress */
        foreach ($addresses as $actualAddress) {
            if (false !== strpos($actualAddress->getText(), $address)) {
                $actualAddress->find('css', '.item-edit-button')->click();

                return;
            }
        }

        throw new ExpectationException(
            sprintf('Address "%s" not found', $address),
            $this->getSession()->getDriver()
        );
    }

    /**
     * Delete address form entity view page by clicking on trash icon given address
     * Example: When I delete Ukraine address
     *
     * @When /^(?:|I )delete (?P<address>[^"]+) address$/
     */
    public function iDeleteAddress($address)
    {
        $addresses = $this->getSession()->getPage()->findAll('css', 'div.map-address-list .map-item');

        /** @var NodeElement $actualAddress */
        foreach ($addresses as $actualAddress) {
            if (false !== strpos($actualAddress->getText(), $address)) {
                $removeButton = $actualAddress->find('css', '.item-remove-button');

                if (null === $removeButton) {
                    throw new ExpectationException(
                        sprintf(
                            'Found address "%s" but it has\'t delete button. Maybe it\'s primary address?',
                            $address
                        ),
                        $this->getSession()->getDriver()
                    );
                }

                $removeButton->click();

                return;
            }
        }

        throw new ExpectationException(
            sprintf('Address "%s" not found', $address),
            $this->getSession()->getDriver()
        );
    }

    /**
     * Assert that entity view page hase default avatar (info-user.png)
     *
     * @Then avatar should be default avatar
     */
    public function avatarShouldBeDefaultAvatar()
    {
        $img = $this->getSession()->getPage()->find('css', 'div.customer-info div.visual img');

        if (false === strpos($img->getAttribute('src'), 'info-user.png')) {
            throw new ExpectationException(
                'Avatar is not default avatar',
                $this->getSession()->getDriver()
            );
        }
    }

    /**
     * Assert that avatar image, e.g. charlie-sheen.jpg
     * Example: And avatar should be "charlie-sheen.jpg"
     *
     * @Then avatar should be :arg1
     */
    public function avatarShouldBe($imgName)
    {
        $img = $this->getSession()->getPage()->find('css', 'div.customer-info div.visual img');

        if (false === strpos($img->getAttribute('src'), $imgName)) {
            throw new ExpectationException(
                sprintf('Avatar is "%s" image', $imgName),
                $this->getSession()->getDriver()
            );
        }
    }

    /**
     * Assert that two accounts set as account at view entity page
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
            if (false === in_array($acc, $accounts, true)) {
                throw new ExpectationException(
                    sprintf('Value "%s" not found in "%s" accounts', $acc, implode(', ', $accounts)),
                    $this->getSession()->getDriver()
                );
            }
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
        $links = $label->getParent()->findAll('css', 'ul.list-inline li a');

        $socialNetworks = [];

        /** @var NodeElement $link */
        foreach ($links as $link) {
            $socialNetworks[$link->getAttribute('title')] = trim($link->getAttribute('href'));
        }

        foreach ($table->getRows() as list($networkName, $networkLink)) {
            if (false === array_key_exists($networkName, $socialNetworks)) {
                throw new ExpectationException(
                    sprintf('%s not found in social networks', $networkName),
                    $this->getSession()->getDriver()
                );
            }
            if ($socialNetworks[$networkName] !== $networkLink) {
                throw new ExpectationException(
                    sprintf(
                        '%s expect to be "%s" but got "%s"',
                        $networkName,
                        $networkLink,
                        $socialNetworks[$networkName]
                    ),
                    $this->getSession()->getDriver()
                );
            }
        }
    }

    /**
     * Assert count of addresses in entity view page
     * Example: And contact has 2 addresses
     * Example: Then contact has one address
     *
     * @Then :count addresses should be in page
     * @Then /^(.*) has (?P<count>(one|two|[\d]+)) address(?:|es)$/
     */
    public function assertAddressCount($count)
    {
        $addresses = $this->getSession()->getPage()->findAll('css', 'div.map-address-list .map-item');
        $currentCount = count($addresses);

        if ($currentCount !== $this->getCount($count)) {
            throw new ExpectationException(
                sprintf('Expect %s addresses but found %s', $count, $currentCount),
                $this->getSession()->getDriver()
            );
        }
    }

    /**
     * Assert that given address is a primary address.
     * Be aware that you can't delete primary address.
     * Example: Then LOS ANGELES address must be primary
     *
     * @Then /^(?P<address>[^"]+) address must be primary$/
     */
    public function assertPrimaryAddress($address)
    {
        $addresses = $this->getSession()->getPage()->findAll('css', 'div.map-address-list .map-item');

        /** @var NodeElement $actualAddress */
        foreach ($addresses as $actualAddress) {
            if (false !== strpos($actualAddress->getText(), $address)) {
                if ('Primary' === $actualAddress->find('css', 'ul.inline')->getText()) {
                    return;
                } else {
                    throw new ExpectationException(
                        sprintf('Address "%s" was found but it is not primary', $address),
                        $this->getSession()->getDriver()
                    );
                }
            }
        }

        throw new ExpectationException(
            sprintf('Address "%s" not found', $address),
            $this->getSession()->getDriver()
        );
    }

    /**
     * Delete all elemets in collection field
     * Example: And I delete all addresses
     *
     * @Given /^(?:|I )delete all (?P<field>[^"]+)$/
     */
    public function iDeleteAllAddresses($field)
    {
        $collection = $this->elementFactory->createElement('OroForm')->findField(ucfirst(Inflector::pluralize($field)));

        /** @var NodeElement $removeButton */
        while ($removeButton = $collection->find('css', '.removeRow')) {
            $removeButton->click();
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
}
