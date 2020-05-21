<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\ImportExport\TemplateFixture;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\ImportExport\TemplateFixture\AddressTypeFixture;
use Oro\Bundle\AddressBundle\ImportExport\TemplateFixture\CountryFixture;
use Oro\Bundle\AddressBundle\ImportExport\TemplateFixture\RegionFixture;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\ImportExport\TemplateFixture\ContactAddressFixture;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateEntityRegistry;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateManager;

class ContactAddressFixtureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContactAddressFixture
     */
    protected $fixture;

    protected function setUp(): void
    {
        $this->fixture = new ContactAddressFixture();
    }

    public function testGetEntityClass()
    {
        $this->assertEquals('Oro\Bundle\ContactBundle\Entity\ContactAddress', $this->fixture->getEntityClass());
    }

    public function testCreateEntity()
    {
        $this->fixture->setTemplateManager($this->getTemplateManager());

        $this->assertEquals(new ContactAddress(), $this->fixture->getEntity('Jerry Coleman'));
    }

    /**
     * @param string $key
     * @param array $types
     * @dataProvider fillEntityDataProvider
     */
    public function testFillEntityData($key, array $types)
    {
        $this->fixture->setTemplateManager($this->getTemplateManager());

        $address = new ContactAddress();
        list($firstName, $lastName) = explode(' ', $key, 2);

        $this->fixture->fillEntityData($key, $address);
        $this->assertEquals($firstName, $address->getFirstName());
        $this->assertEquals($lastName, $address->getLastName());
        $this->assertEquals($types, $address->getTypeNames());
    }

    /**
     * @return array
     */
    public function fillEntityDataProvider()
    {
        return array(
            'Jerry Coleman' => array(
                'key' => 'Jerry Coleman',
                'types' => array(AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING)
            ),
            'John Smith' => array(
                'key' => 'John Smith',
                'types' => array(AddressType::TYPE_BILLING)
            ),
            'John Doo' => array(
                'key' => 'John Doo',
                'types' => array(AddressType::TYPE_SHIPPING)
            ),
        );
    }

    public function testGetData()
    {
        $this->fixture->setTemplateManager($this->getTemplateManager());

        $data = $this->fixture->getData();
        $this->assertCount(1, $data);

        /** @var ContactAddress $address */
        $address = $data->current();
        $this->assertInstanceOf('Oro\Bundle\ContactBundle\Entity\ContactAddress', $address);
        $this->assertEquals('Jerry', $address->getFirstName());
        $this->assertEquals('Coleman', $address->getLastName());
        $this->assertEquals(array(AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING), $address->getTypeNames());
    }

    /**
     * @return TemplateManager
     */
    protected function getTemplateManager()
    {
        $entityRegistry = new TemplateEntityRegistry();
        $templateManager = new TemplateManager($entityRegistry);
        $templateManager->addEntityRepository(new CountryFixture());
        $templateManager->addEntityRepository(new RegionFixture());
        $templateManager->addEntityRepository(new AddressTypeFixture());
        $templateManager->addEntityRepository($this->fixture);

        return $templateManager;
    }
}
