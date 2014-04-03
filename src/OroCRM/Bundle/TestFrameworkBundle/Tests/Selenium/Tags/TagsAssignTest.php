<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\SearchBundle\Tests\Selenium\Pages\Search;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\User;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Users;

/**
 * Class TagsAssignTest
 *
 * @package OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium
 */
class TagsAssignTest extends Selenium2TestCase
{
    /**
     * @return string
     */
    public function testCreateTag()
    {
        $tagName = 'Tag_'.mt_rand();

        $login = $this->login();
        $login->openTags('Oro\Bundle\TagBundle')
            ->add()
            ->assertTitle('Create Tag - Tags - System')
            ->setTagName($tagName)
            ->setOwner('admin')
            ->save()
            ->assertMessage('Tag saved')
            ->assertTitle('Tags - System')
            ->close();

        return $tagName;
    }

    /**
     * @depends testCreateTag
     * @param $tagName
     */
    public function testAccountTag($tagName)
    {
        $accountName = 'Account_'.mt_rand();

        $login = $this->login();

        $login->openAccounts('OroCRM\Bundle\AccountBundle')
            ->add()
            ->setAccountName($accountName)
            ->setOwner('admin')
            ->verifyTag($tagName)
            ->setTag('New_' . $tagName)
            ->save()
            ->assertMessage('Account saved')
            ->toGrid()
            ->close()
            ->filterBy('Account name', $accountName)
            ->open(array($accountName))
            ->verifyTag($tagName);
    }

    /**
     * @depends testCreateTag
     * @param $tagName
     */
    public function testContactTag($tagName)
    {
        $contactName = 'Contact_'.mt_rand();

        $login = $this->login();
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->add()
            ->setFirstName($contactName . '_first')
            ->setLastName($contactName . '_last')
            ->setOwner('admin')
            ->setEmail($contactName . '@mail.com')
            ->verifyTag($tagName)
            ->setTag('New_' . $tagName)
            ->save()
            ->assertMessage('Contact saved')
            ->toGrid()
            ->close()
            ->filterBy('Email', $contactName . '@mail.com')
            ->open(array($contactName))
            ->verifyTag($tagName);
    }

    /**
     * @depends testCreateTag
     * @param $tagName
     */
    public function testUserTag($tagName)
    {
        $userName = 'User_'.mt_rand();

        $login = $this->login();
        /** @var Users $login */
        $login->openUsers('Oro\Bundle\UserBundle')
            ->add()
            ->setUsername($userName)
            ->setOwner('Main')
            ->enable()
            ->setFirstpassword('123123q')
            ->setSecondpassword('123123q')
            ->setFirstName('First_'.$userName)
            ->setLastName('Last_'.$userName)
            ->setEmail($userName.'@mail.com')
            ->setRoles(array('Manager', 'Marketing Manager'), true)
            ->uncheckInviteUser()
            ->verifyTag($tagName)
            ->setTag('New_' . $tagName)
            ->save()
            ->assertMessage('User saved')
            ->toGrid()
            ->close()
            ->filterBy('Username', $userName)
            ->open(array($userName))
            ->verifyTag($tagName);
    }

    /**
     * @depends testCreateTag
     * @depends testAccountTag
     * @depends testContactTag
     * @depends testUserTag
     * @param $tagName
     */
    public function testTagSearch($tagName)
    {
        $this->login();
        $tagSearch = new Search($this);
        $result = $tagSearch->search('New_' . $tagName)
            ->submit()
            ->select('New_' . $tagName)
            ->assertEntity('User', 1)
            ->assertEntity('Contact', 1)
            ->assertEntity('Account', 1);
        $this->assertNotEmpty($result);
    }
}
