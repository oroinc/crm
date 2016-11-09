<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

use Guzzle\Http\Client;
use Guzzle\Plugin\Cookie\Cookie;
use Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;
use Guzzle\Plugin\Cookie\CookiePlugin;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\FormBundle\Tests\Behat\Element\Select2Entity;
use Oro\Bundle\NavigationBundle\Tests\Behat\Element\MainMenu;
use Oro\Bundle\SalesBundle\Tests\Behat\Context\SalesExtension;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderAwareInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderDictionary;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;

class FeatureContext extends OroFeatureContext implements
    FixtureLoaderAwareInterface,
    OroPageObjectAware,
    KernelAwareContext
{
    use FixtureLoaderDictionary, PageObjectDictionary, KernelDictionary, SalesExtension;

    /**
     * @var string Path to saved template
     */
    protected $template;

    /**
     * @var string Path to import file
     */
    protected $importFile;

    /**
     * @Given Account Name is equal to Business Customer name
     */
    public function accountNameEqualToBusinessCustomer()
    {
        $this->fixtureLoader->loadFixtureFile('account_name_equal_to_business_customer_name.yml');
    }

    /**
     * @Then /^I see only Account name in Account\/Customer field choice$/
     */
    public function iSeeAccountNameOnly()
    {
        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        $actualCustomers = $accountField->getSuggestedValues();

        self::assertContains('Samantha Customer', $actualCustomers);
        self::assertNotContains('Samantha Customer (Samantha Customer)', $actualCustomers);
    }

    /**
     * @Given Account :name has no customers
     */
    public function accountHasNoCustomers($name)
    {
        $this->fixtureLoader->load([
            Account::class => [
                uniqid('account_', true) => [
                    'name' => $name,
                    'owner' => '@samantha',
                    'organization' => '@organization'
                ]
            ]
        ]);
    }

    /**
     * @When I select :name
     */
    public function selectAccount($name)
    {
        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        $accountField->fillSearchField($name);
        $results = $accountField->getSuggestions();
        foreach ($results as $result) {
            if (false !== stripos($result->getText(), $name)) {
                $result->click();

                return;
            }
        }
        self::fail('Not found account in suggested variants');
    }

    /**
     * @Then :content Customer was created
     */
    public function customerWasCreated($content)
    {
        /** @var MainMenu $menu */
        $menu = $this->createElement('MainMenu');
        $menu->openAndClick('Customers/ Business Customers');
        $this->waitForAjax();

        $this->assertRowInGrid($content);
    }

    /**
     * @Then :content Account was created
     */
    public function accountWasCreated($content)
    {
        /** @var MainMenu $menu */
        $menu = $this->createElement('MainMenu');
        $menu->openAndClick('Customers/ Accounts');
        $this->waitForAjax();

        $this->assertRowInGrid($content);
    }

    /**
     * @param string $content
     */
    private function assertRowInGrid($content)
    {
        $row = $this->elementFactory
            ->findElementContains('Grid', $content)
            ->findElementContains('GridRow', $content);

        self::assertTrue($row->isValid(), "Can't find '$content' in grid");
    }

    /**
     * @When type :text into Account field
     */
    public function iTypeIntoAccountField($text)
    {
        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        $accountField->fillSearchField($text);
    }

    /**
     * @Then I should see only existing accounts
     */
    public function iShouldSeeOnlyExistingAccounts()
    {
        $existingCustomers = $this->getCustomers('First Sales Channel', 'samantha');

        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        $actualCustomers = $accountField->getSuggestedValues();

        self::assertEquals(
            sort($existingCustomers),
            sort($actualCustomers)
        );
    }

    /**
     * @Then should not see :text account
     */
    public function shouldNotSeeAccount($text)
    {
        /** @var Select2Entity $accountField */
        $accountField = $this->createElement('OroForm')->findField('Account');
        $actualCustomers = $accountField->getSuggestedValues();

        self::assertNotContains($text, $actualCustomers);
    }

    /**
     * @Given /^(?:|I )go to Opportunity Index page$/
     */
    public function iGoToOpportunityIndexPage()
    {
        /** @var MainMenu $mainMenu */
        $mainMenu = $this->createElement('MainMenu');
        $mainMenu->openAndClick("Sales/Opportunities");
    }

    /**
     * @When I download Data Template file
     */
    public function iDownloadDataTemplateFile()
    {
        $importButton = $this->getSession()
            ->getPage()
            ->findLink('Import');
        self::assertNotNull($importButton);

        $importButton
            ->getParent()
            ->find('css', 'a.dropdown-toggle')
            ->click();
        $link = $importButton->getParent()->findLink('Download Data Template');

        self::assertNotNull($link);

        $url = $this->locatePath($this->getContainer()->get('router')->generate(
            'oro_importexport_export_template',
            ['processorAlias' => 'oro_sales_opportunity']
        ));
        $this->template = tempnam(sys_get_temp_dir(), 'opportunity_template_');

        $cookies = $this->getSession()->getDriver()->getWebDriverSession()->getCookie()[0];
        $cookie = new Cookie();
        $cookie->setName($cookies['name']);
        $cookie->setValue($cookies['value']);
        $cookie->setDomain($cookies['domain']);

        $jar = new ArrayCookieJar();
        $jar->add($cookie);

        $client = new Client($this->getSession()->getCurrentUrl());
        $client->addSubscriber(new CookiePlugin($jar));
        $request = $client->get($url, null, ['save_to' => $this->template]);
        $response = $request->send();

        self::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @Then /^(?:|I )don't see (?P<column>([\w\s]+)) column$/
     */
    public function iDonTSeeBbCustomerNameColumn($column)
    {
        $csv = array_map('str_getcsv', file($this->template));
        self::assertNotContains($column, $csv[0]);
    }

    /**
     * @Then /^(?:|I )see (?P<column>([\w\s]+)) column$/
     */
    public function iSeeAccountColumn($column)
    {
        $csv = array_map('str_getcsv', file($this->template));
        self::assertContains($column, $csv[0]);
    }

    /**
     * @Given I fill template with data:
     */
    public function iFillTemplateWithData(TableNode $table)
    {
        $this->importFile = tempnam(sys_get_temp_dir(), 'opportunity_import_data_');
        $fp = fopen($this->importFile, 'w');
        $csv = array_map('str_getcsv', file($this->template));
        $headers = array_shift($csv);
        fputcsv($fp, $headers);

        foreach ($table as $row) {
            $values = [];
            foreach ($headers as $header) {
                $value = '';
                foreach ($row as $rowHeader => $rowValue) {
                    if (preg_match(sprintf('/^%s$/i', $rowHeader), $header)) {
                        $value = $rowValue;
                    }
                }

                $values[] = $value;
            }
            fputcsv($fp, $values);
        }
    }

    /**
     * @When /^(?:|I )import file$/
     */
    public function iImportFile()
    {
        $this->tryImportFile();
        $this->getSession()->getPage()->pressButton('Import');
        $this->waitForAjax();
    }

    /**
     * @When /^(?:|I )try import file$/
     */
    public function tryImportFile()
    {
        $page = $this->getSession()->getPage();
        $page->clickLink('Import');
        $this->waitForAjax();
        $this->createElement('ImportFileField')->attachFile($this->importFile);
        $page->pressButton('Submit');
        $this->waitForAjax();
    }

    /**
     * @Then /^(?:|I )should see validation message "(?P<validationMessage>[^"]+)"$/
     */
    public function iShouldSeeValidationMessage($validationMessage)
    {
        $errorsHolder = $this->createElement('ImportErrors');
        self::assertTrue($errorsHolder->isValid(), 'No import errors found');

        $errors = $errorsHolder->findAll('css', 'ol li');
        $existedErrors = [];

        /** @var NodeElement $error */
        foreach ($errors as $error) {
            $error = $error->getHtml();
            $existedErrors[] = $error;
            if (false !== stripos($error, $validationMessage)) {
                return;
            }
        }

        self::fail(sprintf(
            '"%s" error message not found in errors: "%s"',
            $validationMessage,
            implode('", "', $existedErrors)
        ));
    }

    /**
     * @Then /^(?P<customerName>[\w\s]+) customer has (?P<opportunityName>[\w\s]+) opportunity$/
     */
    public function customerHasOpportunity($customerName, $opportunityName)
    {
        /** @var MainMenu $mainMenu */
        $mainMenu = $this->createElement('MainMenu');
        $mainMenu->openAndClick('Customers/ Business Customers');
        $this->waitForAjax();

        /** @var Grid $grid */
        $grid = $this->createElement('Grid');
        self::assertTrue($grid->isValid(), 'Grid not found');
        $grid->clickActionLink($customerName, 'View');
        $this->waitForAjax();

        /** @var Grid $customerOpportunitiesGrid */
        $customerOpportunitiesGrid = $this->createElement('CustomerOpportunitiesGrid');
        $row = $customerOpportunitiesGrid->getRowByContent($opportunityName);

        self::assertTrue($row->isValid());
    }
}
