<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Context;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridRow;
use Oro\Bundle\FormBundle\Tests\Behat\Element\OroForm;
use Oro\Bundle\NavigationBundle\Tests\Behat\Element\MainMenu;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderAwareInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderDictionary;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;

class SalesContext extends OroFeatureContext implements
    FixtureLoaderAwareInterface,
    OroPageObjectAware
{
    use FixtureLoaderDictionary, PageObjectDictionary;

    /**
     * @Given /^two users (charlie) and (samantha) exists in the system$/
     */
    public function twoUsersExistsInTheSystem()
    {
        $this->fixtureLoader->loadFixtureFile('OroUserBundle:samantha_and_charlie_users.yml');
    }

    /**
     * Create Channel with enabled entities from frontend
     * Example: And "First Sales Channel" is a channel with enabled Business Customer entity
     * Example: And "First Sales Channel" is a channel with enabled Business Customer, Customer User entities
     *
     * @Given /^"(?P<channelName>([\w\s]+))" is a channel with enabled (?P<entities>(.+)) (entities|entity)$/
     */
    public function createChannelWithEnabledEntities($channelName, $entities)
    {
        /** @var MainMenu $menu */
        $menu = $this->createElement('MainMenu');
        $menu->openAndClick('System/ Channels');
        $this->waitForAjax();
        $this->getPage()->clickLink('Create Channel');
        $this->waitForAjax();

        /** @var OroForm $form */
        $form = $this->createElement('OroForm');
        $form->fillField('Name', $channelName);
        $form->fillField('Channel Type', 'Sales');
        $this->waitForAjax();

        /** @var Grid $grid */
        $grid = $this->createElement('ChannelEntitiesGrid');
        $channelEntities = array_map('trim', explode(',', $entities));
        $rowsForDelete = [];

        foreach ($grid->getRows() as $row) {
            foreach ($channelEntities as $key => $channelEntity) {
                if (false !== stripos($row->getText(), $channelEntity)) {
                    unset($channelEntities[$key]);
                    continue 2;
                }
            }

            $rowsForDelete[] = $row;
        }

        /** @var GridRow $row */
        foreach ($rowsForDelete as $row) {
            $row->getActionLink('Delete')->click();
        }

        $entitySelector = $this->elementFactory->findElementContains('EntitySelector', 'Please select entity');

        foreach ($channelEntities as $channelEntity) {
            $entitySelector->click();

            $entityOption = $this->elementFactory->findElementContains('SelectToResultLabel', $channelEntity);
            self::assertTrue(
                $entityOption->isIsset(),
                sprintf('Entity "%s" was not found in entity selector', $channelEntity)
            );
            $entityOption->click();

            $this->getPage()->clickLink('Add');
        }

        $form->saveAndClose();

        $repository = $this->getAppContainer()->get('doctrine')->getRepository(Channel::class);

        $this->fixtureLoader->addReference('first_channel', $repository->findOneBy([]));
    }
}
