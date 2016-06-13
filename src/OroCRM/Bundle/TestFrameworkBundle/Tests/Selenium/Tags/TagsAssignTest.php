<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium\Tags;

use Oro\Bundle\TagBundle\Tests\Selenium\Pages\Tags;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

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
        /* @var Tags $login */
        $login->openTags('Oro\Bundle\TagBundle')
            ->add()
            ->assertTitle('Create Tag - Tags - System')
            ->setTagName($tagName)
            ->setOwner('admin')
            ->save('Save and Close')
            ->assertMessage('Tag saved')
            ->assertTitle('All - Tags - System')
            ->close();

        return $tagName;
    }
}
