<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium\Entity;

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
        $login->openConfigEntities('Oro\Bundle\EntityConfigBundle')
            ->assertTitle('Entity Management - Entities - System')
            ->open(array($entityName))
            ->assertTitle("{$entityName} - Entity Management - Entities - System")
            ->createField()
            ->setFieldName($fieldName)
            ->setType('String')
            ->proceed()
            ->save()
            ->assertMessage('Field saved')
            ->updateSchema()
            ->assertTitle('Entity Management - Entities - System')
            ->close()
            ->openAccounts('OroCRM\Bundle\AccountBundle')
            ->add()
            ->openConfigEntity('Oro\Bundle\EntityConfigBundle', false)
            ->checkEntityField($fieldName);
    }
}
