<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium\Entity;

use Oro\Bundle\EntityConfigBundle\Tests\Selenium\Pages\ConfigEntities;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

/**
 * Class EntityTest
 *
 * @package OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium
 */
class EntityTest extends Selenium2TestCase
{
    public function testEditExistEntity()
    {
        $entityName = 'Account';
        $fieldName = 'test_field' . mt_rand();
        $login = $this->login();
        /** @var  ConfigEntities $login*/
        $login->openConfigEntities('Oro\Bundle\EntityConfigBundle')
            ->assertTitle('All - Entity Management - Entities - System')
            ->filterBy('Name', $entityName)
            ->open(array($entityName))
            ->assertTitle("{$entityName} - Entity Management - Entities - System")
            ->createField()
            ->setFieldName($fieldName)
            ->setStorageType('Table column')
            ->setType('String')
            ->proceed()
            ->save()
            ->assertMessage('Field saved')
            ->updateSchema()
            ->assertTitle('All - Entity Management - Entities - System')
            ->close()
            ->openAccounts('OroCRM\Bundle\AccountBundle')
            ->add()
            ->openConfigEntity('Oro\Bundle\EntityConfigBundle', false)
            ->checkEntityField($fieldName);
    }
}
